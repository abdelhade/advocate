<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $query = Client::query()->latest();

        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('national_id', 'like', "%{$search}%");
        }

        $clients = $query->paginate(15)->through(function ($client) {
            return [
                'id' => $client->id,
                'name' => $client->name,
                'phone' => $client->phone,
                'email' => $client->email,
                'created_at' => $client->created_at->format('Y-m-d'),
            ];
        });

        return Inertia::render('Tenant/Clients/Index', [
            'clients' => $clients,
            'filters' => $request->only('search'),
        ]);
    }

    public function create()
    {
        return Inertia::render('Tenant/Clients/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'national_id' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ], [
            'name.required' => 'اسم الموكل مطلوب.',
            'email.email' => 'يجب إدخال بريد إلكتروني صحيح.',
        ]);

        Client::create($validated);

        return redirect()->route('clients.index')
            ->with('success', 'تم إضافة الموكل بنجاح.');
    }

    public function show(Client $client)
    {
        $client->load('legalCases');

        return Inertia::render('Tenant/Clients/Show', [
            'client' => [
                'id' => $client->id,
                'name' => $client->name,
                'phone' => $client->phone,
                'email' => $client->email,
                'national_id' => $client->national_id,
                'address' => $client->address,
                'notes' => $client->notes,
                'created_at' => $client->created_at->format('Y-m-d'),
                'legal_cases' => $client->legalCases->map(function ($case) {
                    return [
                        'id' => $case->id,
                        'case_number' => $case->case_number,
                        'title' => $case->title,
                        'status' => $case->status,
                        'created_at' => $case->created_at->format('Y-m-d'),
                    ];
                }),
            ],
        ]);
    }

    public function edit(Client $client)
    {
        return Inertia::render('Tenant/Clients/Edit', [
            'client' => [
                'id' => $client->id,
                'name' => $client->name,
                'phone' => $client->phone,
                'email' => $client->email,
                'national_id' => $client->national_id,
                'address' => $client->address,
                'notes' => $client->notes,
            ],
        ]);
    }

    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'national_id' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ], [
            'name.required' => 'اسم الموكل مطلوب.',
            'email.email' => 'يجب إدخال بريد إلكتروني صحيح.',
        ]);

        $client->update($validated);

        return redirect()->route('clients.index')
            ->with('success', 'تم تحديث بيانات الموكل بنجاح.');
    }

    public function destroy(Client $client)
    {
        $client->delete();

        return redirect()->route('clients.index')
            ->with('success', 'تم حذف الموكل بنجاح.');
    }
}
