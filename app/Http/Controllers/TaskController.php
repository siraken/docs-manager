<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use Michelf\Markdown;

class TaskController extends Controller
{
    public function index()
    {
        $tasks = Task::all();
        return view('tasks/index', compact('tasks'));
    }

    public function create(Request $request)
    {
        if ($request->isMethod('POST'))
        {
            $task = new Task();
            if ($task->fill($request->all())->save())
            {
                return redirect('/tasks')->with('flash_message', 'Successful');
            }
        }
        return view('tasks/create');
    }

    public function edit(Request $request)
    {
        if ($request->isMethod('POST'))
        {
            $task = new Task();
            if ($task->fill($request->all())->save())
            {
                return redirect('/tasks')->with('flash_message', 'Successful');
            }
        }
        return view('tasks/create');
    }

    public function view($id = null)
    {
        $task = Task::find($id);
        $task->description = Markdown::defaultTransform($task->description);
        return view('tasks/view', compact('task'));
    }

    public function delete($id = null)
    {
        $task = Task::find($id);
        if ($task->delete()) {
            return redirect('/tasks/')->with('flash_message', 'Successful');
        }
    }
}
