# Task Tracker

This project is a CLI app to track tasks and manage to-do lists.

## Requirements

You will need PHP (V8 or higher) to use this CLI app. I reccomend you XAMPP to use this app.

## Usage
The list of commands and their usage is given below:

### Adding a new task
```
php index.php add "Buy groceries"
```

### Updating and deleting tasks
```
php index.php update 1 "Buy groceries and cook dinner"
php index.php delete 1
```

### Marking a task as in progress or done
```
php index.php mark-in-progress 1
php index.php mark-done 1
```

### Listing all tasks
```
php index.php list
```

### Listing tasks by status
```
php index.php list done
php index.php list todo
php index.php list in-progress
```