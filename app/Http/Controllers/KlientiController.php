<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Klienti;
use Illuminate\Support\Facades\Auth;
use Milon\Barcode\DNS1D;

class KlientiController extends Controller
{
    // Shfaq forma për shtim
    public function A()
    {
        return view('create');
    }

    // Ruajtja e të dhënave të reja
    public function B(Request $request)
    {
        $data = $request->all();

        $qmimi = $request->qmimi;
        $sasia = $request->sasia;

        Klienti::create([
            'emri'           => $data['emri']     ?? '',
            'mbiemri'        => $data['mbiemri']  ?? '',
            'telefoni'       => $data['telefoni'] ?? '',
            'produkti'       => $data['produkti'] ?? '',
            'qmimi'          => $qmimi,
            'sasia'          => $sasia,
            'user_id'        => Auth::id(),
            'data_e_shitjes' => now()->toDateString(),
        ]);

        return redirect()->back()->with('success', 'U shtua me sukses!');
    }

    // Listimi dhe kërkimi i klientëve
    public function C(Request $request)
    {
        $search = $request->input('search');

        if ($search) {
            $results = Klienti::where('user_id', Auth::id())
                ->where('data_e_shitjes', now()->toDateString())
                ->where(function ($query) use ($search) {
                    $query->where('emri', 'like', "%{$search}%")
                          ->orWhere('mbiemri', 'like', "%{$search}%");
                })
                ->get();

            if ($results->count() === 1) {
                $klient   = $results->first();
                $gjithsej = $klient->qmimi * $klient->sasia;

                return view('invoice', compact('klient', 'gjithsej'));
            }

            $klientet = $results;
        } else {
            $klientet = Klienti::where('user_id', Auth::id())
                ->whereDate('data_e_shitjes', now()->toDateString())
                ->get();
        }

        return view('read', compact('klientet'));
    }

    // Shfaq forma për editim
    public function edit($id)
    {
        $klient = Klienti::findOrFail($id);
        return view('edit', compact('klient'));
    }

    // Përditëso të dhënat ekzistuese
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'emri'     => 'required|string|max:255',
            'mbiemri'  => 'required|string|max:255',
            'telefoni' => 'required|string|max:20',
            'produkti' => 'required|string|max:255',
            'sasia'    => 'required|numeric|min:1',
            'qmimi'    => 'required|numeric|min:0',
        ]);

        $klient = Klienti::findOrFail($id);
        $klient->update($data);

        return redirect()->route('user.dashboard')->with('success', 'Të dhënat u përditësuan me sukses!');
    }

    // Fshi një klient
    public function destroy($id)
    {
        $klient = Klienti::findOrFail($id);

        if ($klient->user_id !== Auth::id()) {
            abort(403, 'Akses i ndaluar.');
        }

        $klient->delete();

        return redirect()->route('user.dashboard')->with('success', 'Klienti u fshi me sukses!');
    }

    // Gjenero faturën me barkod
    public function invoice($id)
    {
        $klient = Klienti::findOrFail($id);

        if ($klient->user_id !== Auth::id()) {
            abort(403);
        }

        $generator = new DNS1D();
        $barcode   = $generator->getBarcodePNG(strval($klient->id), 'C128');

        return view('klient.invoice', compact('klient', 'barcode'));
    }
}
