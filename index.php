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
            echo "Updating task\n";
            break;

        case 'delete':
            echo "Deleting task\n";
            break;

        case 'mark':
            $sub_option = $argv[2] ?? null;

            if ($sub_option == 'in-progress') {
                echo "Mark in progress\n";
            } elseif ($sub_option == 'done') {
                echo "Mark done\n";
            } else {
                echo "The option is not valid\n";
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
