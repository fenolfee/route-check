<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TrustedSubnet;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TrustedSubnetController extends Controller
{
    public function index()
    {
        $subnets = TrustedSubnet::query()
            ->orderByDesc('is_enabled')
            ->orderBy('cidr')
            ->paginate(25);

        return view('admin.trusted-subnets.index', compact('subnets'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'cidr'  => ['required','string','max:128','unique:trusted_subnets,cidr', function($attr,$val,$fail){
                if (! $this->isValidCidr($val)) $fail('Некорректный CIDR.');
            }],
            'label' => ['nullable','string','max:255'],
        ]);

        TrustedSubnet::create([
            'cidr'       => $data['cidr'],
            'label'      => $data['label'] ?? null,
            'is_enabled' => true,
            'user_id'    => auth()->id(),
        ]);

        return back()->with('success','Подсеть добавлена.');
    }

    public function update(Request $request, TrustedSubnet $trustedSubnet)
    {
        $data = $request->validate([
            'cidr'       => ['required','string','max:128', Rule::unique('trusted_subnets','cidr')->ignore($trustedSubnet->id), function($attr,$val,$fail){
                if (! $this->isValidCidr($val)) $fail('Некорректный CIDR.');
            }],
            'label'      => ['nullable','string','max:255'],
            'is_enabled' => ['required','boolean'],
        ]);

        $trustedSubnet->update([
            'cidr'       => $data['cidr'],
            'label'      => $data['label'] ?? null,
            'is_enabled' => $data['is_enabled'],
            'user_id'    => auth()->id(),
        ]);

        return back()->with('success','Подсеть обновлена.');
    }

    public function destroy(TrustedSubnet $trustedSubnet)
    {
        $trustedSubnet->delete();
        return back()->with('success','Подсеть удалена.');
    }

    /** Проверка IPv4/IPv6 CIDR */
    private function isValidCidr(string $cidr): bool
    {
        // IPv4 CIDR
        if (preg_match('/^(\d{1,3}\.){3}\d{1,3}\/\d{1,2}$/', $cidr)) {
            [$ip,$mask] = explode('/', $cidr, 2);
            $oct = array_map('intval', explode('.', $ip));
            if (max($oct) > 255 || min($oct) < 0) return false;
            $m = (int)$mask;
            return $m >= 0 && $m <= 32;
        }

        // IPv6 CIDR
        if (preg_match('/^[0-9a-f:]+\/\d{1,3}$/i', $cidr)) {
            [$ip,$mask] = explode('/', $cidr, 2);
            if (@inet_pton($ip) === false) return false;
            $m = (int)$mask;
            return $m >= 0 && $m <= 128;
        }

        return false;
    }
}
