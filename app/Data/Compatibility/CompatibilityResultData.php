<?php

namespace App\Data\Compatibility;

final readonly class CompatibilityResultData
{
    /**
     * @param  list<CompatibilityReasonData>  $positiveReasons
     * @param  list<CompatibilityReasonData>  $warningReasons
     * @param  list<CompatibilityReasonData>  $blockingReasons
     */
    public function __construct(
        public int $score,
        public string $fitStatus,
        public array $positiveReasons = [],
        public array $warningReasons = [],
        public array $blockingReasons = [],
    ) {}

    public function hasBlockingReasons(): bool
    {
        return $this->blockingReasons !== [];
    }

    /**
     * @return array{
     *     score:int,
     *     fit_status:string,
     *     positive_reasons:list<array{key:string,message:string,weight:int,severity:string}>,
     *     warning_reasons:list<array{key:string,message:string,weight:int,severity:string}>,
     *     blocking_reasons:list<array{key:string,message:string,weight:int,severity:string}>
     * }
     */
    public function toArray(): array
    {
        return [
            'score' => $this->score,
            'fit_status' => $this->fitStatus,
            'positive_reasons' => array_map(
                fn (CompatibilityReasonData $reason): array => $reason->toArray(),
                $this->positiveReasons,
            ),
            'warning_reasons' => array_map(
                fn (CompatibilityReasonData $reason): array => $reason->toArray(),
                $this->warningReasons,
            ),
            'blocking_reasons' => array_map(
                fn (CompatibilityReasonData $reason): array => $reason->toArray(),
                $this->blockingReasons,
            ),
        ];
    }
}
