<?php

namespace App\Console\Commands;

use App\Models\CourseCoupon;
use Carbon\Carbon;
use Illuminate\Console\Command;

class HandleCouponsIfExpired extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'coupon:handleCouponsIfExpired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Handle coupons if expired';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $result = CourseCoupon::where("status", 1)
            ->whereHas('coupon', function ($queryCoupon) {
                $queryCoupon
                    ->where(function ($q) {
                        $q
                            ->where('enrollment_limit', '<>', 0)
                            ->whereColumn('course_coupon.currently_enrolled', 'enrollment_limit');
                    })
                    ->orWhere(function ($q) {
                        $q
                            ->whereDate('course_coupon.expires', '<=', Carbon::now('Asia/Ho_Chi_Minh'))
                            ->whereTime('course_coupon.expires', '<=', Carbon::now('Asia/Ho_Chi_Minh'));
                    });
            })
            ->update(['status' => 0]);

        $this->info('number of coupons expired: ' . $result . ' at: ' . Carbon::now('Asia/Ho_Chi_Minh')->isoFormat('DD/MM/YYYY HH:mm A'));
    }
}
