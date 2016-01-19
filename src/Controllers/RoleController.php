<?php

namespace Centaur\Controllers;

use Sentinel;
use App\Http\Requests;
use Illuminate\Http\Request;
use Cartalyst\Sentinel\Users\IlluminateUserRepository;

class RoleController extends Controller
{
    /** @var Cartalyst\Sentinel\Users\IlluminateRoleRepository */
    protected $roleRepository;

    public function __construct()
    {
        // Middleware
        $this->middleware('sentinel.auth');
        $this->middleware('sentinel.role:administrator');

        // Fetch the Role Repository from the IoC container
        $this->roleRepository = app()->make('sentinel.roles');
    }

    /**
     * Display a listing of the roles.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $roles = $this->roleRepository->createModel()->all();

        return view('Centaur::roles.index')
            ->with('roles', $roles);
    }

    /**
     * Show the form for creating a new role.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('Centaur::roles.create');
    }

    /**
     * Store a newly created role in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validate the form data
        $result = $this->validate($request, [
            'name' => 'required',
            'slug' => 'required|alpha_dash|unique:roles',
        ]);

        // Create the Role
        $role = Sentinel::getRoleRepository()->createModel()->create([
            'name' => trim($request->get('name')),
            'slug' => trim($request->get('slug')),
        ]);

        // Cast permissions values to boolean
        $permissions = [];
        foreach ($request->get('permissions', []) as $permission => $value) {
            $permissions[$permission] = (bool)$value;
        }

        // Set the role permissions
        $role->permissions = $permissions;
        $role->save();

         // All done
        if ($request->ajax()) {
            return response()->json(['role' => $role], 200);
        }

        session()->flash('success', "Role '{$role->name}' has been created.");
        return redirect()->route('roles.index');
    }

    /**
     * Display the specified role.
     *
     * @param  string  $hash
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // The roles detail page has not been included for the sake of brevity.
        // Change this to point to the appropriate view for your project.
        return redirect()->route('roles.index');
    }

    /**
     * Show the form for editing the specified role.
     *
     * @param  string  $hash
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // Fetch the role object
        // $id = $this->decode($hash);
        $role = $this->roleRepository->findById($id);

        if ($role) {
            return view('Centaur::roles.edit')
                ->with('role', $role);
        }

        session()->flash('error', 'Invalid role.');
        return redirect()->back();
    }

    /**
     * Update the specified role in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $hash
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Decode the role id
        // $id = $this->decode($hash);

        // Validate the form data
        $result = $this->validate($request, [
            'name' => 'required',
            'slug' => 'required|alpha_dash|unique:roles,slug,'.$id,
        ]);

        // Fetch the role object
        $role = $this->roleRepository->findById($id);
        if (!$role) {
            if ($request->ajax()) {
                return response()->json("Invalid role.", 422);
            }
            session()->flash('error', 'Invalid role.');
            return redirect()->back()->withInput();
        }

        // Update the role
        $role->name = $request->get('name');
        $role->slug = $request->get('slug');

        // Cast permissions values to boolean
        $permissions = [];
        foreach ($request->get('permissions', []) as $permission => $value) {
            $permissions[$permission] = (bool)$value;
        }

        // Set the role permissions
        $role->permissions = $permissions;
        $role->save();

        // All done
        if ($request->ajax()) {
            return response()->json(['role' => $role], 200);
        }

        session()->flash('success', "Role '{$role->name}' has been updated.");
        return redirect()->route('roles.index');
    }

    /**
     * Remove the specified role from storage.
     *
     * @param  string  $hash
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        // Fetch the role object
        // $id = $this->decode($hash);
        $role = $this->roleRepository->findById($id);

        // Remove the role
        $role->delete();

        // All done
        $message = "Role '{$role->name}' has been removed.";
        if ($request->ajax()) {
            return response()->json([$message], 200);
        }

        session()->flash('success', $message);
        return redirect()->route('roles.index');
    }

    /**
     * Decode a hashid
     * @param  string $hash
     * @return integer|null
     */
    // protected function decode($hash)
    // {
    //     $decoded = $this->hashids->decode($hash);

    //     if (!empty($decoded)) {
    //         return $decoded[0];
    //     } else {
    //         return null;
    //     }
    // }
}
