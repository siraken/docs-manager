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
        $task = new Task();

        if ($request->isMethod('POST'))
        {
            if ($task->fill($request->all())->save())
            {
                return redirect('/tasks')->with('flash_message', 'Successful');
            }
        }
        return view('tasks/form', compact('task'));
    }

    public function edit(Request $request, $id = null)
    {
        $task = Task::find($id);

        if ($request->isMethod('POST'))
        {
            if ($task->fill($request->all())->save())
            {
                return redirect('/tasks')->with('flash_message', 'Successful');
            }
        }
        return view('tasks/form', compact('task'));
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
