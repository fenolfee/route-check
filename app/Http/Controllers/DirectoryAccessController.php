<?php

namespace App\Http\Controllers;

use App\Models\DirectoryAccessRule;
use App\Support\DirectoryAccessResolver;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DirectoryAccessController extends Controller
{
    public function __construct(protected DirectoryAccessResolver $resolver) {}

    public function edit(Request $request)
    {
        
        $rel = trim((string)$request->query('path', ''), "/\\");
        $rule = DirectoryAccessRule::firstOrNew(['path' => $rel], [
            'access' => 'closed',
            'trusted_subnets' => [],
        ]);

        return view('files.access', [
            'rel' => $rel,
            'rule' => $rule,
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'path'   => ['required','string'],
            'access' => ['required', Rule::in(['open','trusted','closed'])],
            'trusted_subnets' => ['nullable','string'], // comma/space/line-separated
        ]);

        $subnets = $this->parseSubnets($data['trusted_subnets'] ?? '');

        $rule = DirectoryAccessRule::updateOrCreate(
            ['path' => trim($data['path'], "/\\")],
            [
                'access' => $data['access'],
                'trusted_subnets' => $subnets,
                'user_id' => auth()->id(),
            ]
        );

        return redirect()
            ->route('files.index', ['path' => $rule->path])
            ->with('success', 'Настройки доступа сохранены.');
    }

    protected function parseSubnets(string $raw): array
    {
        $parts = preg_split('/[\s,;]+/u', trim($raw));
        $parts = array_values(array_filter($parts, fn($v) => $v !== ''));
        return $parts;
    }
}
