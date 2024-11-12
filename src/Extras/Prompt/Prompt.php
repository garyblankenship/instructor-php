<?php

namespace Cognesy\Instructor\Extras\Prompt;

use Cognesy\Instructor\Extras\Prompt\Contracts\CanHandleTemplate;
use Cognesy\Instructor\Extras\Prompt\Data\PromptEngineConfig;
use Cognesy\Instructor\Utils\Messages\Message;
use Cognesy\Instructor\Utils\Messages\Messages;
use Cognesy\Instructor\Utils\Messages\Script;
use Cognesy\Instructor\Utils\Str;
use Cognesy\Instructor\Utils\Xml\Xml;
use Cognesy\Instructor\Utils\Xml\XmlElement;
use InvalidArgumentException;

class Prompt
{
    const DSN_SEPARATOR = ':';

    private PromptLibrary $library;
    private PromptInfo $promptInfo;

    private string $templateContent;
    private array $variableValues;
    private string $rendered;
    private $tags = ['chat', 'message', 'content', 'section'];

    public function __construct(
        string             $path = '',
        string             $library = '',
        PromptEngineConfig $config = null,
        CanHandleTemplate  $driver = null,
    ) {
        $this->library = new PromptLibrary($library, $config, $driver);
        $this->templateContent = $path ? $this->library->loadTemplate($path) : '';
    }

    public static function twig() : self {
        return new self(config: PromptEngineConfig::twig());
    }

    public static function blade() : self {
        return new self(config: PromptEngineConfig::blade());
    }

    public static function make(string $pathOrDsn) : Prompt {
        return match(true) {
            Str::contains($pathOrDsn, self::DSN_SEPARATOR) => self::fromDsn($pathOrDsn),
            default => new self(path: $pathOrDsn),
        };
    }

    public static function using(string $library) : Prompt {
        return new self(library: $library);
    }

    public static function text(string $pathOrDsn, array $variables) : string {
        return self::make($pathOrDsn)->withValues($variables)->toText();
    }

    public static function messages(string $pathOrDsn, array $variables) : Messages {
        return self::make($pathOrDsn)->withValues($variables)->toMessages();
    }

    public static function fromDsn(string $dsn) : Prompt {
        if (!Str::contains($dsn, self::DSN_SEPARATOR)) {
            throw new InvalidArgumentException("Invalid DSN: $dsn - missing separator");
        }
        $parts = explode(self::DSN_SEPARATOR, $dsn, 2);
        if (count($parts) !== 2) {
            throw new InvalidArgumentException("Invalid DSN: `$dsn` - failed to parse");
        }
        return new self(path: $parts[1], library: $parts[0]);
    }

    public function withLibrary(string $library) : self {
        $this->library->get($library);
        return $this;
    }

    public function withConfig(PromptEngineConfig $config) : self {
        $this->library->withConfig($config);
        return $this;
    }

    public function withDriver(CanHandleTemplate $driver) : self {
        $this->library->withDriver($driver);
        return $this;
    }

    public function get(string $path) : self {
        return $this->withTemplate($path);
    }

    public function withTemplate(string $path) : self {
        $this->templateContent = $this->library->loadTemplate($path);
        $this->promptInfo = new PromptInfo($this->templateContent, $this->library->config());
        return $this;
    }

    public function withTemplateContent(string $content) : self {
        $this->templateContent = $content;
        $this->promptInfo = new PromptInfo($this->templateContent, $this->library->config());
        return $this;
    }

    public function with(array $values) : self {
        return $this->withValues($values);
    }

    public function from(string $string) : self {
        $this->withTemplateContent($string);
        return $this;
    }
    public function withValues(array $values) : self {
        $this->variableValues = $values;
        return $this;
    }

    public function toText() : string {
        return $this->rendered();
    }

    public function toMessages() : Messages {
        return $this->makeMessages($this->rendered());
    }

    public function toScript() : Script {
        return $this->makeScript($this->rendered());
    }

    public function toArray() : array {
        return $this->toMessages()->toArray();
    }

    public function config() : PromptEngineConfig {
        return $this->library->config();
    }

    public function params() : array {
        return $this->variableValues;
    }

    public function template() : string {
        return $this->templateContent;
    }

    public function variables() : array {
        return $this->library->getVariableNames($this->templateContent);
    }

    public function info() : PromptInfo {
        return $this->promptInfo;
    }

    public function validationErrors() : array {
        $infoVars = $this->info()->variableNames();
        $templateVars = $this->variables();
        $valueKeys = array_keys($this->variableValues);
        return $this->validateVariables($infoVars, $templateVars, $valueKeys);
    }

    // INTERNAL ///////////////////////////////////////////////////

    private function rendered() : string {
        if (!isset($this->rendered)) {
            $rendered = $this->library->renderString($this->templateContent, $this->variableValues);
            $this->rendered = $rendered;
        }
        return $this->rendered;
    }

    private function makeMessages(string $text) : Messages {
        return match(true) {
            $this->containsXml($text) && $this->hasChatRoles($text) => $this->makeMessagesFromXml($text),
            default => Messages::fromString($text),
        };
    }

    private function makeScript(string $text) : Script {
        return match(true) {
            $this->containsXml($text) && $this->hasChatRoles($text) => $this->makeScriptFromXml($text),
            default => Messages::fromString($text),
        };
    }

    private function hasChatRoles(string $text) : bool {
        $roleStrings = [
            '<chat>', '<message>', '<section>'
        ];
        if (Str::containsAny($text, $roleStrings)) {
            return true;
        }
        return false;
    }

    private function containsXml(string $text) : bool {
        return preg_match('/<[^>]+>/', $text) === 1;
    }

    private function makeScriptFromXml(string $text) : Script {
        $xml = Xml::from($text)->withTags($this->tags)->toXmlElement();
        $script = new Script();
        $section = $script->section('messages');
        foreach ($xml->children() as $element) {
            if ($element->tag() === 'section') {
                $section = $script->section($element->attribute('name') ?? 'messages');
                continue;
            }
            if ($element->tag() !== 'message') {
                continue;
            }
            $section->appendMessage(Message::make(
                role: $element->attribute('role', 'user'),
                content: match(true) {
                    $element->hasChildren() => $this->getMessageContent($element),
                    default => $element->content(),
                }
            ));
        }
        return $script;
    }

    private function makeMessagesFromXml(string $text) : Messages {
        $xml = Xml::from($text)->withTags($this->tags)->toXmlElement();
        $messages = new Messages();
        foreach ($xml->children() as $element) {
            if ($element->tag() !== 'message') {
                continue;
            }
            $messages->appendMessage(Message::make(
                role: $element->attribute('role', 'user'),
                content: match(true) {
                    $element->hasChildren() => $this->getMessageContent($element),
                    default => $element->content(),
                }
            ));
        }
        return $messages;
    }

    private function getMessageContent(XmlElement $element) : array {
        $content = [];
        foreach ($element->children() as $child) {
            if ($child->tag() !== 'content') {
                continue;
            }
            // check if content type is text, image or audio
            $type = $child->attribute('type', 'text');
            $content[] = match($type) {
                'image' => ['type' => 'image_url', 'image_url' => ['url' => $child->content()]],
                'audio' => ['type' => 'input_audio', 'input_audio' => ['data' => $child->content(), 'format' => $child->attribute('format', 'mp3')]],
                'text' => ['type' => 'text', 'text' => $child->content()],
                default => throw new InvalidArgumentException("Invalid content type: $type"),
            };
        }
        return $content;
    }

    private function validateVariables(array $infoVars, array $templateVars, array $valueKeys) : array {
        $messages = [];
        foreach($infoVars as $var) {
            if (!in_array($var, $valueKeys)) {
                $messages[] = "$var: variable defined in template info, but value not provided";
            }
            if (!in_array($var, $templateVars)) {
                $messages[] = "$var: variable defined in template info, but not used";
            }
        }
        foreach($valueKeys as $var) {
            if (!in_array($var, $infoVars)) {
                $messages[] = "$var: value provided, but not defined in template info";
            }
            if (!in_array($var, $templateVars)) {
                $messages[] = "$var: value provided, but not used in template content";
            }
        }
        foreach($templateVars as $var) {
            if (!in_array($var, $infoVars)) {
                $messages[] = "$var: variable used in template, but not defined in template info";
            }
            if (!in_array($var, $valueKeys)) {
                $messages[] = "$var: variable used in template, but value not provided";
            }
        }
        return $messages;
    }
}