<?php
namespace App\Http\Controllers;

use App\Models\Archive;
use App\Models\Mandat;
use Illuminate\Http\Request;

class ArchiveController extends Controller
{
    public function index(Request $request)
    {
        $query = Archive::latest();
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        $archives = $query->paginate(15)->withQueryString();
        return view('archives.index', compact('archives'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'titre'         => ['required', 'string', 'max:255'],
            'type'          => ['required', 'in:' . implode(',', Archive::TYPES)],
            'reference'     => ['nullable', 'string', 'max:255'],
            'description'   => ['nullable', 'string'],
            'date_document' => ['nullable', 'date'],
        ]);
        $data['mandat_id'] = Mandat::actif()?->id;
        Archive::create($data);
        return back()->with('ok', 'Document archivé.');
    }

    public function destroy(Archive $archive)
    {
        $archive->delete();
        return back()->with('ok', 'Document supprimé.');
    }
}
