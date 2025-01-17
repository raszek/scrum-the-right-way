<?php

namespace App\Service\Issue;

class StoryPointService
{

    /**
     * @return int[]
     */
    public function recommendedStoryPoints(): array
    {
        return [
            1,
            2,
            3,
            5,
            8,
            13,
            20,
            40,
            100
        ];
    }

}
