const taskInput = document.getElementById("taskinput");
const addButton = document.getElementById("addbutton");
const taskList = document.getElementById("tasklist");
const taskCount = document.getElementById("taskcount");

let remainingTasks = 0;

addButton.addEventListener("click", function () {

    const task = taskInput.value.trim();

    if (task === "") {
        alert("Please enter a task");
        return;
    }

    const li = document.createElement("li");

    const taskText = document.createElement("span");
    taskText.textContent = task;

    const deleteButton = document.createElement("button");
    deleteButton.textContent = "Delete";

    // Click the task to mark it as completed
    taskText.addEventListener("click", function () {

        taskText.style.textDecoration =
            taskText.style.textDecoration === "line-through"
            ? "none"
            : "line-through";

    });

    // Delete task
    deleteButton.addEventListener("click", function () {

        li.remove();

        remainingTasks--;

        taskCount.textContent =
            "Remaining Task : " + remainingTasks;
    });

    li.appendChild(taskText);
    li.appendChild(deleteButton);

    taskList.appendChild(li);

    remainingTasks++;

    taskCount.textContent =
        "Remaining Task : " + remainingTasks;

    taskInput.value = "";
});