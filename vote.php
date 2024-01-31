
<?php
session_start();

require_once 'db.php';

$vote = null; // Initialize $vote to null

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pollId = $_POST['poll_id'];
    $optionId = $_POST['option_id'];
    $userId = $_SESSION['userId'];

    // Check if the user has already voted
    $stmt = $conn->prepare("SELECT * FROM user_polls WHERE poll_id = ? AND user_id = ?");
    $stmt->execute([$pollId, $userId]);
    $vote = $stmt->fetch();

    if ($vote && $vote['has_voted']) {
        // If the user has already voted, prevent them from voting again
        echo 'You have already voted for this poll.';
    
        // Fetch the option that the user voted on
        $stmt = $conn->prepare("SELECT * FROM votes WHERE id = ?");
        $stmt->execute([$vote['option_id']]);
        $votedOption = $stmt->fetch();
    
        // Display the option that the user voted on
        echo 'You voted for: ' . $votedOption['option_name'];
    
        header("refresh:3;url=viewpolls.html");
        exit;
    } else {
        // If the user hasn't voted yet, insert their vote
        $stmt = $conn->prepare("INSERT INTO user_polls (poll_id, option_id, user_id, has_voted) VALUES (?, ?, ?, 1) ON DUPLICATE KEY UPDATE option_id = ?, has_voted = 1");
        $stmt->execute([$pollId, $optionId, $userId, $optionId]);

        // Update the vote count in the votes table
        $stmt = $conn->prepare("UPDATE votes SET votes = votes + 1 WHERE id = ?");
        $stmt->execute([$optionId]);

        echo 'Your vote has been submitted.';
        header("refresh:3;url=viewpolls.html");
        exit;
    }
}


$pollId = $_GET['pollId'];

$stmt = $conn->prepare("SELECT * FROM polls WHERE id = ?");
$stmt->execute([$pollId]);
$poll = $stmt->fetch();

$stmt = $conn->prepare("SELECT * FROM votes WHERE poll_id = ?");
$stmt->execute([$pollId]);
$options = $stmt->fetchAll();

if ($vote && $vote['has_voted']) {
    // If the user has already voted, get the option they have voted on
    $stmt = $conn->prepare("SELECT * FROM votes WHERE id = ?");
    $stmt->execute([$vote['option_id']]);
    $votedOption = $stmt->fetch();
}
?>

<h2><?php echo $poll['name']; ?></h2>
<p><?php echo $poll['description']; ?></p>

<?php if (isset($votedOption)): ?>
    <p>You have voted for: <?php echo $votedOption['option_name']; ?></p>
<?php else: ?>
    <form method="POST" action="vote.php">
        <input type="hidden" name="poll_id" value="<?php echo $pollId; ?>">
        <?php foreach ($options as $option): 
            $votedClass = '';
            if ($vote && $vote['option_id'] == $option['id']) {
                $votedClass = 'voted';
            }
        ?>
            <div class="<?php echo $votedClass; ?>">
                <input type="radio" id="option<?php echo $option['id']; ?>" name="option_id" value="<?php echo $option['id']; ?>">
                <label for="option<?php echo $option['id']; ?>"><?php echo $option['option_name']; ?></label>
            </div>
        <?php endforeach; ?>
        <input type="submit" value="Vote">
    </form>
<?php endif; ?>