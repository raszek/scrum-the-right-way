<?php

namespace App\Enum\Thread;

enum ThreadStatusEnum: int
{
    
    case Open = 1;
    
    case Closed = 2;

    public function label(): string
    {
        return match ($this) {
            ThreadStatusEnum::Open => 'Open',
            ThreadStatusEnum::Closed => 'Closed',
        };
    }

}
