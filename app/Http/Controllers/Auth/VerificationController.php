<?php

//namespace App\Http\Controllers\Auth;

//use App\Http\Controllers\Controller;
//use Illuminate\Http\Request;
//use Illuminate\Support\Facades\Auth;
//use App\Models\User;

//class VerificationController extends Controller
//{
    /**
     * Menampilkan form untuk memasukkan kode verifikasi.
     */
  //  public function showForm()
    //{
      //  return view('auth.verify-code');
    //}

    /**
     * Memverifikasi kode yang dikirim ke email.
     */
    //public function verify(Request $request)
   // {
     //   $request->validate([
       //     'verification_code' => 'required|numeric',
        //]);

        //$user = Auth::user();

        // Pastikan user yang login valid
        //if ($user instanceof User) {
          //  if ($request->verification_code == $user->verification_code) {
                // Update status email terverifikasi
            //    $user->email_verified_at = now();
              //  $user->verification_code = null; // Kosongkan kode setelah dipakai
                //$user->save(); // Simpan perubahan

                //return redirect()->route('dashboard')->with('success', 'Email berhasil diverifikasi.');
           // }

            //return back()->withErrors(['verification_code' => 'Kode verifikasi salah.']);
        //}

        //return back()->withErrors(['user' => 'User tidak valid atau tidak ditemukan.']);
    //}
//}
