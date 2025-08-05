<?php

namespace BoilingSoup\Sneeze\Console;

use BoilingSoup\Sneeze\VerificationCode;
use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'sneeze:prune-stale')]
class PruneStaleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sneeze:prune-stale';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prune used or expired email verification / password reset codes';

    /**
     * Execute the console command.
     *
     * @return int|null
     */
    public function handle()
    {
        VerificationCode::query()
            ->where('is_used', true)
            ->orWhere('expires_at', '<=', now())
            ->delete();
    }
}
