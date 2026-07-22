<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class DataTableDemoController extends Controller
{
    public function __invoke(Request $request)
    {
        // 1. Generate 45 items of mock data
        $users = $this->getMockUsers();

        // 2. Determine mode (client-side vs server-side demo)
        $mode = $request->query('mode', 'client'); // 'client' or 'server'

        if ($mode === 'server') {
            // Apply server-side searching, sorting, and pagination
            $query = $request->query('search', '');
            $sort = $request->query('sort', 'name');
            $direction = $request->query('direction', 'asc');
            $perPage = (int) $request->query('perPage', 10);
            $page = (int) $request->query('page', 1);

            // Filtering
            $filtered = $users;
            if (!empty($query)) {
                $queryLower = strtolower($query);
                $filtered = $users->filter(function ($user) use ($queryLower) {
                    return str_contains(strtolower($user['name']), $queryLower) ||
                           str_contains(strtolower($user['email']), $queryLower) ||
                           str_contains(strtolower($user['role']), $queryLower) ||
                           str_contains(strtolower($user['status']), $queryLower);
                });
            }

            // Sorting
            $filtered = $filtered->sortBy(function ($user) use ($sort) {
                return $user[$sort] ?? '';
            }, SORT_REGULAR, $direction === 'desc');

            // Pagination
            $total = $filtered->count();
            $sliced = $filtered->slice(($page - 1) * $perPage, $perPage)->values();

            $paginated = new LengthAwarePaginator(
                $sliced,
                $total,
                $perPage,
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );

            $data = $paginated;
        } else {
            // Client side mode: pass everything directly to Alpine
            $data = $users;
        }

        // Define table columns
        $columns = [
            ['key' => 'name', 'label' => 'Name & Email', 'sortable' => true, 'type' => 'user_profile', 'emailKey' => 'email'],
            ['key' => 'role', 'label' => 'Role', 'sortable' => true, 'type' => 'text'],
            ['key' => 'status', 'label' => 'Status', 'sortable' => true, 'type' => 'badge'],
            ['key' => 'joined_date', 'label' => 'Joined Date', 'sortable' => true, 'type' => 'text'],
            ['key' => 'actions', 'label' => 'Actions', 'sortable' => false, 'align' => 'right', 'type' => 'actions'],
        ];

        return view('reports.data-table-demo', [
            'data' => $data,
            'columns' => $columns,
            'mode' => $mode,
            'serverSide' => $mode === 'server',
        ]);
    }

    private function getMockUsers(): Collection
    {
        $roles = ['Admin', 'Manager', 'Developer', 'Designer', 'Support'];
        $statuses = ['active', 'pending', 'inactive'];
        $names = [
            'Alexander Wright', 'Sophia Martinez', 'Liam Henderson', 'Olivia Jenkins',
            'Noah Patterson', 'Emma Watson', 'Jackson Davies', 'Ava Robinson',
            'Lucas Wood', 'Isabella Smith', 'Mason Jones', 'Mia Taylor',
            'Ethan Brown', 'Amelia Miller', 'Oliver Davis', 'Charlotte Wilson',
            'James Evans', 'Harper Thomas', 'Benjamin Roberts', 'Evelyn Johnson',
            'William Carter', 'Abigail Mitchell', 'Michael Perez', 'Emily Roberts',
            'Daniel Gomez', 'Elizabeth Phillips', 'Henry Campbell', 'Sofia Parker',
            'Matthew Evans', 'Avery Stewart', 'Wyatt Flores', 'Ella Morris',
            'David Rogers', 'Madison Hughes', 'Carter Washington', 'Scarlett Butler',
            'Jayden Simmons', 'Victoria Foster', 'Gabriel Simmons', 'Aria Bryant',
            'John Doe', 'Jane Smith', 'Alice Cooper', 'Bob Dylan', 'Charlie Parker'
        ];

        $users = collect();
        foreach ($names as $idx => $name) {
            $firstName = strtolower(explode(' ', $name)[0]);
            $users->push([
                'id' => $idx + 1,
                'name' => $name,
                'email' => "{$firstName}." . ($idx + 10) . "@example.com",
                'role' => $roles[$idx % count($roles)],
                'status' => $statuses[$idx % count($statuses)],
                'joined_date' => date('Y-m-d', strtotime("-{$idx} months -{$idx} days")),
            ]);
        }

        return $users;
    }
}
