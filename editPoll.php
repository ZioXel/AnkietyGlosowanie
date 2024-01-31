<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Polls</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .form-container {
            width: 400px;
            padding: 20px;
            background-color: white;
            border-radius: 4px;
            box-shadow: 0px 0px 10px 0px rgba(0, 0, 0, 0.1);
        }

        .form-container h2 {
            text-align: center;
            color: #333;
        }

        .form-container label {
            display: block;
            margin-bottom: 10px;
        }

        .form-container input[type="text"],
        .form-container input[type="number"] {
            width: 90%;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }

        .form-container .options-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .options-container input[type="text"],
        .options-container input[type="number"] {
            width: 48%; /* Adjust width as needed */
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }

        .form-container .users-container {
            overflow-y: auto;
            max-height: 100px; /* Adjust the maximum height as needed */
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 10px;
            margin-bottom: 10px;
        }

        .user-container label {
            display: block;
            margin-bottom: 5px;
        }

        .form-container input[type="submit"] {
            width: 100%;
            padding: 10px;
            border-radius: 4px;
            border: none;
            background-color: #007BFF;
            color: white;
            cursor: pointer;
        }

        .form-container input[type="submit"]:hover {
            background-color: #0056b3;
        }

        .delete-form {
            margin-top: 10px;
        }

        .delete-form input[type="submit"] {
            background-color: #dc3545;
        }

        .delete-form input[type="submit"]:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>

<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pollId = $_POST['pollId'];

    if (isset($_POST['delete'])) {
        $stmt = $conn->prepare("DELETE FROM votes WHERE poll_id = ?");
        $stmt->execute([$pollId]);

        $stmt = $conn->prepare("DELETE FROM polls WHERE id = ?");
        $stmt->execute([$pollId]);

        header('Location: ManagePools.html');
    } else {
        $name = $_POST['name'];
        $description = $_POST['description'];
        $quorum = $_POST['quorum'];

        $stmt = $conn->prepare("UPDATE polls SET name = ?, description = ?, quorum = ? WHERE id = ?");
        $stmt->execute([$name, $description, $quorum, $pollId]);

        foreach ($_POST['options'] as $optionName => $optionValue) {
            $votes = $_POST['votes'][$optionName];

            $stmt = $conn->prepare("UPDATE votes SET votes = ? WHERE poll_id = ? AND option_name = ?");
            $stmt->execute([$votes, $pollId, $optionValue]);
        }
        $stmt = $conn->prepare("DELETE FROM user_polls WHERE poll_id = ?");
        $stmt->execute([$pollId]);
        // After updating the poll and its options, assign the users
        if (isset($_POST['users'])) {
            foreach ($_POST['users'] as $userId) {
                // Replace this with your own logic to assign the user to the poll
                $stmt = $conn->prepare("INSERT INTO user_polls (user_id, poll_id) VALUES (?, ?)");
                $stmt->execute([$userId, $pollId]);
            }
        }

        header('Location: ManagePolls.html');
    }
}
else {
    $pollId = $_GET['pollId'];

    $stmt = $conn->prepare("SELECT polls.*, votes.option_name, votes.votes FROM polls LEFT JOIN votes ON polls.id = votes.poll_id WHERE polls.id = ?");
    $stmt->execute([$pollId]);
    $poll = $stmt->fetch(PDO::FETCH_ASSOC);

    $options = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $optionsInputs = '';
    foreach ($options as $option) {
        $optionsInputs .= "
            <div class='options-container'>
                <input type='text' name='options[{$option['option_name']}]' value='{$option['option_name']}' placeholder='Option'>
                <input type='number' name='votes[{$option['option_name']}]' value='{$option['votes']}' placeholder='Votes'>
            </div>";
    }

    // Fetch the list of users
    $stmt = $conn->prepare("SELECT * FROM users");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch the list of users assigned to this poll
    $stmt = $conn->prepare("SELECT user_id FROM user_polls WHERE poll_id = ?");
    $stmt->execute([$pollId]);
    $assignedUsers = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

    // Generate the users inputs
    $usersInputs = '';
    foreach ($users as $user) {
        $checked = in_array($user['id'], $assignedUsers) ? 'checked' : '';
        $usersInputs .= "
            <div class='user-container'>
                <label>
                    <input type='checkbox' name='users[]' value='{$user['id']}' $checked>
                    {$user['username']}
                </label>
            </div>";
    }

    echo "
        <div class='form-container'>
            <h2>Edit Poll</h2>
            <form method='POST'>
                <input type='hidden' name='pollId' value='{$poll['id']}'>
                <label>Name: <input type='text' name='name' value='{$poll['name']}'></label>
                <label>Description: <input type='text' name='description' value='{$poll['description']}'></label>
                <label>Quorum: <input type='number' name='quorum' value='{$poll['quorum']}'></label>
                <div class='options-container'>
                    <label>Options</label>
                    <label>Votes</label>
                </div>
                {$optionsInputs}
                <div class='users-container'>
                    <label>Assign to users</label>
                    {$usersInputs}
                </div>
                <input type='submit' value='Update Poll'>
            </form>
            <form action='deletePoll.php' method='post' class='delete-form'>
                <input type='hidden' name='pollId' value='{$poll['id']}'>
                <input type='submit' value='Delete Poll'>
            </form>
            <form action='generateRandomVotes.php' method='post' class='random-votes-form'>
                <input type='hidden' name='pollId' value='{$poll['id']}'>
                <input type='submit' value='Generate Random Votes'>
            </form>
        </div>";
}
?>

</body>
</html>
