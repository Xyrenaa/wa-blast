<?php

namespace App\Http\Controllers;

use App\Models\MessageTemplate;
use App\Support\TemplateRenderer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MessageTemplateController extends Controller
{
    private const CATEGORIES = ['Keuangan', 'Akademik', 'Kegiatan', 'Lainnya'];

    // Halaman daftar sekaligus form template baru
    public function index(Request $r)
    {
        return $this->page($r, new MessageTemplate());
    }

    // Halaman daftar sekaligus form edit
    public function edit(Request $r, MessageTemplate $template)
    {
        return $this->page($r, $template);
    }

    public function store(Request $r)
    {
        MessageTemplate::create($this->data($r));

        return redirect()->route('templates.index')->with('success', 'Template berhasil disimpan.');
    }

    public function update(Request $r, MessageTemplate $template)
    {
        $template->update($this->data($r));

        return redirect()->route('templates.index')->with('success', 'Template diperbarui.');
    }

    public function destroy(MessageTemplate $template)
    {
        $template->delete();

        return redirect()->route('templates.index')->with('success', 'Template dihapus.');
    }

    private function page(Request $r, MessageTemplate $editing)
    {
        $templates = MessageTemplate::query()
            ->when($r->q, fn ($x, $s) => $x->where(fn ($w) => $w->where('nama', 'like', "%$s%")->orWhere('isi', 'like', "%$s%")))
            ->latest('updated_at')
            ->paginate(10)
            ->withQueryString();

        return view('templates.index', [
            'templates' => $templates,
            'editing' => $editing,
            'categories' => self::CATEGORIES,
            'studentVars' => TemplateRenderer::STUDENT_VARS,
            'sample' => TemplateRenderer::sample(),
        ]);
    }

    private function data(Request $r): array
    {
        return $r->validate([
            'nama' => ['required', 'string', 'max:255'],
            'kategori' => ['nullable', Rule::in(self::CATEGORIES)],
            'isi' => ['required', 'string', 'max:4000'],
        ]);
    }
}
