<?php

/**
 * Interfaces
 */
interface IFile
{
    public function getFileSize();
    public function save($content);
    public function read();
}

/**
 * Classes
 */
class JsonFile implements IFile
{
    private $filename;

    public function __construct($filename)
    {
        $this->filename = $filename;
    }

    public function getFileSize()
    {
        return filesize($this->getFullFilename());
    }

    public function save($content)
    {
        $handle = $this->open(mode: 'w+');

        $content_encoded = json_encode($content);

        fwrite($handle, $content_encoded);

        $this->close($handle);
    }

    public function read()
    {
        $handle = $this->open();

        $filse_size = $this->getFileSize();

        if ($filse_size > 0) {
            $content = fread($handle, $filse_size);
        } else {
            $content = json_encode([]);
        }

        $this->close($handle);

        return json_decode($content, true);
    }

    private function open($mode = 'r+')
    {
        return fopen(filename: $this->getFullFilename(), mode: $mode);
    }

    private function close($handle)
    {
        return fclose($handle);
    }

    private function getFullFilename()
    {
        return __DIR__ . DIRECTORY_SEPARATOR . $this->filename;
    }
}

class Task
{
    private $id;
    private $description;
    private $status;

    public function __construct($id, $description, $status)
    {
        $this->id = $id;
        $this->description = $description;
        $this->status = $status;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getStatus()
    {
        return $this->status;
    }
}

class TaskList
{
    private $file;

    public function __construct(IFile $file)
    {
        $this->file = $file;
    }

    public function add(Task $task)
    {
        $tasks = $this->file->read();

        $current_date = date('Y-m-d', strtotime('now'));

        $tasks[] = [
            'id' => $task->getId(),
            'description' => $task->getDescription(),
            'status' => $task->getStatus(),
            'createdAt' => $current_date,
            'updatedAt' => $current_date,
        ];

        $this->file->save($tasks);
    }

    public function getNextId()
    {
        $tasks = $this->file->read();

        if (count($tasks) > 0) {
            $last_task = end($tasks);

            return intval($last_task['id']) + 1;
        } else {
            return 1;
        }
    }

    public function getTask($id)
    {
        $tasks = $this->file->read();

        foreach ($tasks as $task) {
            if ($task['id'] == $id) {
                return $task;
            }
        }
    }

    public function update($task_update)
    {
        $tasks = $this->file->read();
        $current_date = date('Y-m-d', strtotime('now'));
        foreach ($tasks as &$task) {
            if ($task['id'] == $task_update['id']) {
                $task['description'] = $task_update['description'];
                $task['updatedAt'] = $current_date;
                break;
            }
        }
        $this->file->save($tasks);
    }

    public function delete($task_to_delete)
    {
        $tasks = $this->file->read();
        foreach ($tasks as $id => $task) {
            if ($task['id'] == $task_to_delete['id']) {
                unset($tasks[$id]);
                break;
            }
        }
        $this->file->save($tasks);
    }

    public function markInProgress($task_update)
    {
        $tasks = $this->file->read();
        $current_date = date('Y-m-d', strtotime('now'));
        foreach ($tasks as &$task) {
            if ($task['id'] == $task_update['id']) {
                $task['status'] = 'in-progress';
                $task['updatedAt'] = $current_date;
                break;
            }
        }
        $this->file->save($tasks);
    }

    public function markDone($task_update)
    {
        $tasks = $this->file->read();
        $current_date = date('Y-m-d', strtotime('now'));
        foreach ($tasks as &$task) {
            if ($task['id'] == $task_update['id']) {
                $task['status'] = 'done';
                $task['updatedAt'] = $current_date;
                break;
            }
        }
        $this->file->save($tasks);
    }
}

/**
 * Main
 */
$option = $argv[1] ?? null;

if ($option) {
    switch ($option) {
        case 'add':
            echo "Adding task...\n";
            $description = $argv[2] ?? null;

            if ($description) {
                $file = new JsonFile('tasks.json');
                $file_size = $file->getFileSize();

                if ($file_size > 0) {
                    $taskList = new TaskList($file);
                    $nextId = $taskList->getNextId();
                    $task = new Task(id: $nextId, description: $description, status: 'todo');
                    $taskList->add($task);

                    echo "The task has been added sucessfully!\n";
                } else {
                    $task = new Task(id: 1, description: $description, status: 'todo');
                    $taskList = new TaskList($file);
                    $taskList->add($task);

                    echo "The task has been added sucessfully!\n";
                }
            } else {
                echo "You need to enter the task description\n";
            }
            break;

        case 'update':
            echo "Updating task...\n";
            $id = (int) $argv[2] ?? null;
            $description = $argv[3] ?? null;

            if ($id && $description) {
                $file = new JsonFile('tasks.json');
                $file_size = $file->getFileSize();
                if ($file_size > 0) {
                    $taskList = new TaskList(file: $file);
                    $task = $taskList->getTask(id: $id);
                    if ($task) {
                        $task['description'] = $description;
                        $taskList->update($task);
                        echo "The task has been updated sucessfully!\n";
                    } else {
                        echo "Task not found\n";
                    }
                } else {
                    echo "There is no tasks available\n";
                }
            } else {
                echo "The arguments are invalid. You need to set the task id and then the task description\n";
            }
            break;

        case 'delete':
            echo "Deleting task...\n";
            $id = (int) $argv[2] ?? null;

            if ($id) {
                $file = new JsonFile('tasks.json');
                $file_size = $file->getFileSize();
                if ($file_size > 0) {
                    $taskList = new TaskList(file: $file);
                    $task = $taskList->getTask(id: $id);
                    if ($task) {
                        $taskList->delete($task);
                        echo "The task has been deleted sucessfully!\n";
                    } else {
                        echo "Task not found\n";
                    }
                } else {
                    echo "There is no tasks available\n";
                }
            } else {
                echo "You need to set the task id\n";
            }

            break;

        case 'mark-in-progress':
            echo "Marking task as in progress...\n";
            $id = (int) $argv[2] ?? null;

            if ($id) {
                $file = new JsonFile('tasks.json');
                $file_size = $file->getFileSize();
                if ($file_size > 0) {
                    $taskList = new TaskList(file: $file);
                    $task = $taskList->getTask(id: $id);
                    if ($task) {
                        $taskList->markInProgress($task);
                        echo "The task has been updated sucessfully!\n";
                    } else {
                        echo "Task not found\n";
                    }
                } else {
                    echo "There is no tasks available\n";
                }
            } else {
                echo "You need to set the task id\n";
            }
            break;

        case 'mark-done':
            echo "Marking task as done...\n";
            $id = (int) $argv[2] ?? null;

            if ($id) {
                $file = new JsonFile('tasks.json');
                $file_size = $file->getFileSize();
                if ($file_size > 0) {
                    $taskList = new TaskList(file: $file);
                    $task = $taskList->getTask(id: $id);
                    if ($task) {
                        $taskList->markDone($task);
                        echo "The task has been updated sucessfully!\n";
                    } else {
                        echo "Task not found\n";
                    }
                } else {
                    echo "There is no tasks available\n";
                }
            } else {
                echo "You need to set the task id\n";
            }
            break;

        case 'list':
            $sub_option = $argv[2] ?? null;

            if ($sub_option == 'in-progress') {
                echo "Listing in progress tasks\n";
            } elseif ($sub_option == 'done') {
                echo "Listing done tasks\n";
            } elseif ($sub_option == 'todo') {
                echo "Listing not done tasks\n";
            } else {
                echo "Listing all tasks\n";
            }
            break;
        
        default:
            echo "The option is not valid\n";
            break;
    }
} else {
    echo "The option is not valid\n";
}
