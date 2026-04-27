<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Admin;
use App\Models\MembershipTier;
use App\Notifications\AdminAlertNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncUserTiersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600;

    public function handle(): void
    {
        try {
            $tiers = MembershipTier::orderBy('min_spent', 'desc')->get();

            if ($tiers->isEmpty()) {
                return;
            }

            User::chunk(200, function ($users) use ($tiers) {
                foreach ($users as $user) {
                    $newTierId = null;

                    foreach ($tiers as $tier) {
                        if ($user->total_spent >= $tier->min_spent) {
                            $newTierId = $tier->id;
                            break;
                        }
                    }

                    if ($newTierId && $user->membership_tier_id !== $newTierId) {
                        $user->update(['membership_tier_id' => $newTierId]);
                    }
                }
            });

            $admins = Admin::where('role_id', 1)->where('status', 'active')->get();
            foreach ($admins as $admin) {
                $admin->notify(new AdminAlertNotification([
                    'type'    => 'success',
                    'title'   => 'Đồng bộ Hạng Thành Viên hoàn tất',
                    'message' => 'Hệ thống đã quét và cập nhật lại hạng cho toàn bộ khách hàng dựa trên hạn mức chi tiêu mới.',
                    'url'     => '/admin/users'
                ]));
            }
        } catch (\Exception $e) {
            Log::error('SyncUserTiersJob Error: ' . $e->getMessage());
        }
    }
}