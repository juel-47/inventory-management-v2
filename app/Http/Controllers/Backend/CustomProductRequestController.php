<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\CustomProductRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Str;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class CustomProductRequestController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            // No strict global middleware here because methods have internal checks
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = CustomProductRequest::with(['user'])->orderBy('id', 'desc');
        
        // Only Admin role can see all requests. Others only see their own.
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->hasRole('Admin')) {
            $query->where('user_id', Auth::id());
        }

        $customProductRequests = $query->get();
        return view('backend.custom-product-request.index', compact('customProductRequests'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Any user with Create permission can create requests
        if (!$user->can('Create Custom Product Requests')) {
             abort(403, 'You do not have permission to create custom product requests.');
        }

        $users = [];
        if ($user->can('Manage Custom Product Requests')) {
            $users = \App\Models\User::where('status', 1)->orderBy('name', 'asc')->get();
        }

        return view('backend.custom-product-request.create', compact('users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->can('Create Custom Product Requests') && !$user->can('Manage Custom Product Requests')) {
            abort(403);
        }

        $request->validate([
            'product_description' => 'required|string|min:10',
            'product_name' => 'nullable|string|max:255',
            'example_image' => 'nullable|array',
            'example_image.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'quantity_needed' => 'required|integer|min:1',
            'expected_price' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $customRequest = new CustomProductRequest();
            $customRequest->request_no = 'CPR-' . strtoupper(Str::random(10));
            
            // Assign user_id: use input if admin provided it, otherwise use Auth::id()
            if ($user->can('Manage Custom Product Requests') && $request->has('user_id')) {
                $customRequest->user_id = $request->user_id;
            } else {
                $customRequest->user_id = Auth::id();
            }

            $customRequest->product_name = $request->product_name;
            $customRequest->product_description = $request->product_description;
            $customRequest->quantity_needed = $request->quantity_needed;
            $customRequest->expected_price = $request->expected_price;
            $customRequest->status = 'pending';

            // Handle image upload
            if ($request->hasFile('example_image')) {
                $paths = [];
                foreach ($request->file('example_image') as $image) {
                    if (!$image || !$image->isValid()) {
                        continue;
                    }
                    $imageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
                    $image->move(public_path('uploads/custom_product_requests'), $imageName);
                    $paths[] = 'uploads/custom_product_requests/' . $imageName;
                }
                if (!empty($paths)) {
                    $customRequest->example_image = json_encode($paths);
                }
            }

            $customRequest->save();

            DB::commit();
            toastr()->success('Custom Product Request submitted successfully!');
            return redirect()->route('admin.custom-product-requests.index');

        } catch (\Exception $e) {
            DB::rollBack();
            toastr()->error('Something went wrong: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $customProductRequest = CustomProductRequest::with(['user'])->findOrFail($id);
        
        /** \App\Models\User $user */
        $user = Auth::user();
        
        // Only Admin role can view any request. Others can only view their own.
        if (!$user->hasRole('Admin') && $customProductRequest->user_id != Auth::id()) {
            abort(403, 'Unauthorized access to this custom product request.');
        }

        return view('backend.custom-product-request.show', compact('customProductRequest'));
    }

    /**
     * Update the status of the request.
     */
    public function updateStatus(Request $request, $id)
    {
        $customRequest = CustomProductRequest::findOrFail($id);
        
        // Only Admin/Manager can update status
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->can('Manage Custom Product Requests')) {
            abort(403);
        }

        $request->validate([
            'status' => 'required|in:pending,approved,rejected',
            'admin_note' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            $customRequest->update([
                'status' => $request->status,
                'admin_note' => $request->admin_note
            ]);
            
            DB::commit();
            toastr()->success('Custom Product Request updated successfully!');
            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollBack();
            toastr()->error('Something went wrong: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $customRequest = CustomProductRequest::findOrFail($id);
        
        // Authorization: Manager can delete anything, User can only delete own pending requests
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->can('Manage Custom Product Requests') && ($customRequest->user_id != Auth::id() || $customRequest->status !== 'pending')) {
             return response(['status' => 'error', 'message' => 'Unauthorized or request already processed']);
        }

        // Delete associated image if exists
        if ($customRequest->example_image && file_exists(public_path($customRequest->example_image))) {
            unlink(public_path($customRequest->example_image));
        }

        $customRequest->delete();
        return response(['status' => 'success', 'message' => 'Deleted Successfully!']);
    }
}
