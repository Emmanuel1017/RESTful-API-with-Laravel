<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Todo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Http\Requests\TodoUpdateRequest;
use App\Http\Requests\TodoAddRequest;
use Illuminate\Support\Facades\Gate;
use Cviebrock\EloquentSluggable\Services\SlugService;

use App\Http\Resources\UserResource;

class TodoController extends Controller
{
    // init constructor
    public function __construct()
    {
        // validate authenticated users
        $this->middleware('auth:api');
    }

    //get all items
    public function index()
    {
        //where to query only users items
        $todos = Todo::all()->where('user_id', '=', Auth::user()->id);
        $count = $todos->count();
        if ($count < 1) {
            return response()->json([
                'response' => 'user has no items',
            ]);
        } else {
            return response()->json([
                'status' => 'success',
                'user items' => $count,
                'items array' => $todos,
            ]);
        }
    }

    //save item
    public function create_item(TodoAddRequest $request)
    {
        // Get the currently authenticated user's ID...
        $id = Auth::user()->id;

        if ($todo = Todo::create([
            'user_id' => $id,
            'title' => $request->title,
            'description' => $request->description,
        ])) {
            return response()->json([
                'status' => 'success',
                'message' => 'Item created successfully',
                'todo' => $todo,
            ]);
        } else {
            return response()->json([
                'status' => 'failed',
                'message' => 'Item not created',
                'todo' => $todo,
            ]);
        }
    }

    //get specific item
    public function show($id)
    {
        try {
            $todo = Todo::findOrFail($id);
            //$todo = Todo::where('id', '=', $id)->get();
        } catch (\Exception$e) {
            return response()->json(['message' => 'item not found!'], 404);
        }

        if (Gate::allows('can_get', $todo)) {
            return response()->json([
                'status' => $id,
                'todo' => $todo,
            ]);
        } else {
            return response()->json([
                'status' => 'not allowed to view item',
            ]);
        }
    }

    //edit
    public function update(TodoUpdateRequest $request, $id)
    {
        /* try {
             $request->validate([
                 'user_id' => 'integer',
                 'title' => 'required|string|max:255',
                 'description' => 'required|string|max:255',
             ]);
         } catch (Exception $ex) {
             return response()->json([

                 'message' => $ex,
             ]);
         }*/

        try {
            $todo = Todo::findOrFail($id);
        } catch (Exception $ex) {
            return response()->json([
                'status' => 'Error',
                'message' => $ex,
            ]);
        }

        $todo->title = $request->title;
        $todo->description = $request->description;

        if ($todo->save()) {
            return response()->json([
                'status' => 'success',
                'message' => 'updated successfully',
                'todo' => $todo,
            ]);
        }
    }

    //delete
    public function destroy($id)
    {
        // $affectedRows = Todo::where('id', '=', $id)->where('user_id', '=', Auth::user()->id);

        try {
            $todo = Todo::findOrFail($id);
            //$todo = Todo::where('id', '=', $id)->get();
        } catch (\Exception$e) {
            return response()->json(['message' => 'item not found!'], 404);
        }

        $user = Auth::user();

        if (Gate::allows('can_delete', $todo)) {
            if ($todo->delete()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Item With Index: ' . $id . ' deleted successfully',
                ]);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Deleting of ' . $id . ' failed ',

                ]);
            }
        } else {
            if (Gate::allows('isAdmin', $user) || Gate::allows('isManager', $user)) {
                if ($todo->delete()) {
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Item With Index: ' . $id . ' deleted successfully',

                    ]);
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'Deleting of ' . $id . ' failed ',

                    ]);
                }
            }
        }
    }


    //---------------------------------misc admin functions------------------------------------------------------------//


    public function get_specific_user_todos($id)
    {
        $current_user = Auth::user();
        if (Gate::allows('isAdmin', $current_user) || Gate::allows('isManager', $current_user)) {
            try {
                $todos = Todo::where('user_id', '=', $id)->get();
            } catch (\Exception$e) {
                return response()->json(['message' => 'item not found!'], 404);
            }
            //use first if returning collection to return single object  -> use first to get an object not collection
            $user = User::where('id', '=', $id)->get()->first();

            $user = new UserResource($user);
            $count_todos = $todos->count();

            if ($count_todos < 1) {
                $todos = 'user has no items';
            }

            return response()->json([
                'status' => 'success',
                //  'message' => 'user: '.$user->name,
                'user' => $user,
                'todos' => $todos,
            ]);
        } else {
            return response()->json([
                'status' => 'failed',
                'message' => 'only admins can do this',
            ]);
        }
    }


    public function get_all_users_admin(Request $request)
    {
        $user = Auth::user();
        if (Gate::allows('isAdmin', $user) || Gate::allows('isManager', $user)) {
            if ($users = User::all()) {
                $count = $users->count();
                if ($count < 1) {
                    return response()->json([
                        'response' => 'no users',
                    ]);
                } else {
                    return response()->json([
                        'status' => 'success',
                        'No of users :' => $count.' users',
                        'Users: ' => $users
                    ]);
                }
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Fetch method failed',

                ]);
            }
        } else {
            return response()->json([
                'status' => 'failed',
                'message' => 'You must be admin or superuser to view this',

            ]);
        }
    }

    public function get_item_count(Request $request)
    {
        $user = Auth::user();
        if (Gate::allows('isAdmin', $user) || Gate::allows('isManager', $user)) {
            if ($todos = TOdo::all()) {
                $count = $todos->count();
                if ($count < 1) {
                    return response()->json([
                        'response' => 'no items',
                    ]);
                } else {
                    return response()->json([
                        'status' => 'success',
                        'No of items :' => $count.' items',
                        'items' => $todos
                    ]);
                }
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Fetch method failed',

                ]);
            }
        } else {
            return response()->json([
                'status' => 'failed',
                'message' => 'You must be admin or superuser to view this',

            ]);
        }
    }

    public function get_all_users_and_their_items(Request $request)
    {
        $user = Auth::user();
        if (Gate::allows('isAdmin', $user) || Gate::allows('isManager', $user)) {
            if ($users = User::all()) {
                $count = $users->count();

                if ($count < 1) {
                    return response()->json([
                        'response' => 'no users',
                    ]);
                } else {
                    $response =  array();
                    //iterate through all users
                    foreach ($users as $user) {
                        $todos = Todo::all()->where('user_id', '=', $user->id);
                        $count = $todos->count();

                        if ($count < 1) {
                            array_push($response, 'user: '.$user->name .' has no items', );
                        } else {
                            array_push($response, response()->json([
                                'status: ' => true,
                                'User: ' => $user,
                                $user->name .' items count: ' => $count,
                                $user->name .' items: ' => $todos,
                            ]));

                            // array_push($response,$user->toJson(JSON_PRETTY_PRINT));
                        }
                    }

                    return response()->json([
                        $response
                    ]);
                }
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Fetch method failed',

                ]);
            }
        } else {
            return response()->json([
                'status' => 'failed',
                'message' => 'You must be admin or superuser to view this',

            ]);
        }
    }


 /// file mgmt
    public function upload(Request $request)
    {
        if ($request->hasFile('image')) {
            $filename = $request->image->getClientOriginalName();
            $request->image->storeAs('images', $filename, 'public');
            Auth()->user()->update(['image' => $filename]);
        }
        return redirect()->back();
    }
}
