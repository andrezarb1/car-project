<?php

namespace App\Http\Controllers;

use App\Models\Dealer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DealerController extends Controller
{
    public function index(Request $request)
    {
        // Basic sorting support (nice for marks later)
        $sort = $request->get('sort', 'name');
        $dir  = $request->get('dir', 'asc');

        // Allow only safe sort columns
        if (!in_array($sort, ['name', 'created_at'])) {
            $sort = 'name';
        }
        if (!in_array($dir, ['asc', 'desc'])) {
            $dir = 'asc';
        }

        $dealers = Dealer::orderBy($sort, $dir)->paginate(10);

        return view('dealers.index', compact('dealers', 'sort', 'dir'));
    }

    public function create()
    {
        return view('dealers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'email'    => 'nullable|email|max:255',
        ]);

        // SEO-friendly slug from name
        $validated['slug'] = $this->uniqueSlugFromName($validated['name']);

        Dealer::create($validated);

        return redirect()->route('dealers.index')
            ->with('success', 'Dealer created successfully!');
    }

    public function show(Dealer $dealer)
    {
        return view('dealers.show', compact('dealer'));
    }

    public function edit(Dealer $dealer)
    {
        return view('dealers.edit', compact('dealer'));
    }

    public function update(Request $request, Dealer $dealer)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'email'    => 'nullable|email|max:255',
        ]);

        $validated['slug'] = $this->uniqueSlugFromName($validated['name'], $dealer->id);

        $dealer->update($validated);

        return redirect()->route('dealers.index')
            ->with('success', 'Dealer updated successfully!');
    }

    public function destroy(Dealer $dealer)
    {
        $dealer->delete();

        return redirect()->route('dealers.index')
            ->with('success', 'Dealer deleted successfully!');
    }

    /**
     * Generate a unique slug from a name.
     * If $ignoreId is provided, it won't consider that record (useful for update).
     */
    private function uniqueSlugFromName(string $name, ?int $ignoreId = null): string
    {
        $slug = Str::slug($name);
        $base = $slug;
        $i = 1;

        $query = Dealer::where('slug', $slug);
        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        while ($query->exists()) {
            $slug = $base . '-' . $i++;
            $query = Dealer::where('slug', $slug);
            if ($ignoreId) {
                $query->where('id', '!=', $ignoreId);
            }
        }

        return $slug;
    }
}
