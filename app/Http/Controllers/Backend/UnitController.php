<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Unit\UnitStoreRequest;
use App\Http\Requests\Unit\UnitUpdateRequest;
use App\DataTables\UnitDataTable;
use App\Models\Unit;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Str;

class UnitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(UnitDataTable $dataTable)
    {
        return $dataTable->render('backend.unit.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('backend.unit.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UnitStoreRequest $request)
    {
        $unit = new Unit();
        $unit->name = $request->name;
        $unit->slug = Str::slug($request->name);
        $unit->status = $request->status;
        $unit->save();

        Toastr::success('Unit Created Successfully!');
        return redirect()->route('admin.units.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $unit = Unit::findOrFail($id);
        return view('backend.unit.edit', compact('unit'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UnitUpdateRequest $request, string $id)
    {
        $unit = Unit::findOrFail($id);
        $unit->name = $request->name;
        $unit->slug = Str::slug($request->name);
        $unit->status = $request->status;
        $unit->save();

        Toastr::success('Unit Updated Successfully!');
        return redirect()->route('admin.units.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $unit = Unit::findOrFail($id);
        $unit->delete();

        return response(['status' => 'success', 'message' => 'Deleted Successfully!']);
    }

    public function changeStatus(Request $request)
    {
        $unit = Unit::findOrFail($request->id);
        $unit->status = $request->status == 'true' ? 1 : 0;
        $unit->save();

        return response(['status' => 'success', 'message' => 'Status Updated Successfully!']);
    }
}
