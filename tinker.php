<?php
Stancl\Tenancy\Database\Models\Domain::first()->tenant->run(function () {
    $u = App\Models\User::firstOrCreate(
        ['email' => 'lawyer@test.com'],
        ['name' => 'Lawyer', 'password' => bcrypt('password')]
    );
    echo $u->id;
});
