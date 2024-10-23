<?php

namespace Cognesy\Instructor\Extras\Evals\Observers;

use Cognesy\Instructor\Extras\Evals\Aggregators\AggregateExperimentObservation;
use Cognesy\Instructor\Extras\Evals\Contracts\CanObserveExperiment;
use Cognesy\Instructor\Extras\Evals\Enums\NumberAggregationMethod;
use Cognesy\Instructor\Extras\Evals\Experiment;
use Cognesy\Instructor\Extras\Evals\Observation;

class ExperimentLatency implements CanObserveExperiment
{
    public function observe(Experiment $experiment): Observation {
        return (new AggregateExperimentObservation(
            name: 'experiment.latency_p95',
            observationKey: 'execution.timeElapsed',
            params: [
                'percentile' => 95,
                'unit' => 'seconds',
                'format' => '%.2f',
            ],
            method: NumberAggregationMethod::Percentile,
        ))->observe($experiment);
    }
}