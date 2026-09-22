<?php

namespace App\Services;

use Carbon\Carbon;
use App\Helpers\SalesHelper;
use App\Models\Payment;
use App\Models\SalesOrder;

class NotificationService
{
    private static function filePath(): string
    {
        return storage_path('app/notifications.json');
    }

    public static function getForCurrentUser(): array
    {
        $role = self::getCurrentRole();
        $userKey = self::getCurrentUserKey();
        
        $notifications = self::readAll();

        $userNotifs = [];
        $unreadCount = 0;

        foreach ($notifications as $n) {
            $targetRole = $n['target_role'] ?? 'all';
            $targetUser = $n['target_user'] ?? null;

            $roleMatches = ($targetRole === 'all' || $targetRole === $role);
            $userMatches = (empty($targetUser) || $targetUser === $userKey);

            if ($roleMatches && $userMatches) {
                $readBy = $n['read_by'] ?? [];
                $isRead = in_array($userKey, $readBy, true);

                if (!$isRead) {
                    $unreadCount++;
                }

                $n['is_read'] = $isRead;
                $userNotifs[] = $n;
            }
        }

        $dynamicNotifs = self::getDynamicNotifications($role, $userKey);
        foreach ($dynamicNotifs as $dn) {
            $unreadCount++;
            array_unshift($userNotifs, $dn);
        }

        return [
            'notifications' => array_slice($userNotifs, 0, 20),
            'unread_count'  => $unreadCount,
        ];
    }

    public static function send(array $data): void
    {
        $newNotif = [
            'id'          => 'notif_' . time() . '_' . uniqid(),
            'type'        => $data['type'] ?? 'info',
            'target_role' => $data['target_role'] ?? 'all',
            'target_user' => $data['target_user'] ?? null,
            'title'       => $data['title'] ?? 'Notifikasi',
            'message'     => $data['message'] ?? '',
            'url'         => $data['url'] ?? '#',
            'icon'        => $data['icon'] ?? '🔔',
            'created_at'  => now()->format('Y-m-d H:i:s'),
            'read_by'     => [],
        ];

        $all = self::readAll();
        array_unshift($all, $newNotif);

        $cutoff = Carbon::now()->subDays(30);
        $all = array_filter($all, function ($item) use ($cutoff) {
            if (empty($item['created_at'])) return false;
            return Carbon::parse($item['created_at'])->gte($cutoff);
        });

        if (count($all) > 100) {
            $all = array_slice($all, 0, 100);
        }

        self::saveAll($all);
    }

    public static function markAsRead(?string $notifId = null): void
    {
        $userKey = self::getCurrentUserKey();
        $all = self::readAll();

        foreach ($all as &$n) {
            if ($notifId === 'all' || empty($notifId) || ($n['id'] ?? '') === $notifId) {
                if (!isset($n['read_by']) || !is_array($n['read_by'])) {
                    $n['read_by'] = [];
                }
                if (!in_array($userKey, $n['read_by'], true)) {
                    $n['read_by'][] = $userKey;
                }
            }
        }

        self::saveAll($all);
    }

    private static function getDynamicNotifications(string $role, string $userKey): array
    {
        $dynamic = [];

        if ($role === 'admin') {
            $pendingCount = Payment::where('STATUS', 'pending_approval')->count();
            if ($pendingCount > 0) {
                $dynamic[] = [
                    'id'          => 'dyn_pending_pay',
                    'type'        => 'payment_pending',
                    'target_role' => 'admin',
                    'target_user' => null,
                    'title'       => 'Persetujuan Pembayaran',
                    'message'     => "Terdapat {$pendingCount} titip pembayaran menunggu persetujuan admin.",
                    'url'         => route('admin.payments'),
                    'icon'        => '⏳',
                    'created_at'  => now()->format('Y-m-d H:i:s'),
                    'is_read'     => false,
                    'is_dynamic'  => true,
                ];
            }
        } elseif ($role === 'sales') {
            $kdPeg = SalesHelper::kdPeg();
            if ($kdPeg) {
                $today = Carbon::today()->toDateString();
                $overdueOrders = SalesOrder::where('ST_JADI', 'OS')
                    ->where('KD_PEG', $kdPeg)
                    ->whereRaw("CAST(TANGGAL AS DATE) <= ?", [$today])
                    ->count();

                if ($overdueOrders > 0) {
                    $dynamic[] = [
                        'id'          => 'dyn_overdue_orders',
                        'type'        => 'due_warning',
                        'target_role' => 'sales',
                        'target_user' => $userKey,
                        'title'       => 'Order Jatuh Tempo',
                        'message'     => "Anda memiliki {$overdueOrders} sales order pending yang telah jatuh tempo.",
                        'url'         => route('sales.tagihan.index', ['tab' => 'overdue']),
                        'icon'        => '⚠️',
                        'created_at'  => now()->format('Y-m-d H:i:s'),
                        'is_read'     => false,
                        'is_dynamic'  => true,
                    ];
                }
            }
        }

        return $dynamic;
    }

    private static function readAll(): array
    {
        $path = self::filePath();
        if (!file_exists($path)) {
            return [];
        }

        $content = @file_get_contents($path);
        if (empty($content)) {
            return [];
        }

        $json = json_decode($content, true);
        return is_array($json) ? $json : [];
    }

    private static function saveAll(array $items): void
    {
        $path = self::filePath();
        $dir = dirname($path);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        $json = json_encode(array_values($items), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        if (strlen($json) > 500000 && count($items) > 20) {
            $items = array_slice($items, 0, 20);
            $json = json_encode(array_values($items), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }

        @file_put_contents($path, $json, LOCK_EX);
    }

    private static function getCurrentRole(): string
    {
        if (auth()->guard('sales')->check()) {
            return 'sales';
        }
        $user = auth()->user();
        return $user?->role ?? 'sales';
    }

    private static function getCurrentUserKey(): string
    {
        $kdPeg = SalesHelper::kdPeg();
        if ($kdPeg) {
            return $kdPeg;
        }
        $user = auth()->user();
        return (string) ($user?->NM_USER ?? $user?->id ?? 'guest');
    }
}
