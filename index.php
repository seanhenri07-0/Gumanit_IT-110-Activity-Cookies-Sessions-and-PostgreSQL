
<?php
session_start();
require "db.php";

// SESSION: Count page visits
if ($_SERVER["REQUEST_METHOD"] === "GET") {

    if (!isset($_SESSION["visits"])) {
        $_SESSION["visits"] = 1;
    } else {
        $_SESSION["visits"]++;
    }

}

// POST: Save a new message
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $author = trim($_POST["author"]);
    $body = trim($_POST["body"]);

    // COOKIE: Remember the user's name
    if (isset($_POST["remember"])) {

        setcookie(
            "remembered_name",
            $author,
            time() + (30 * 24 * 60 * 60),
            "/Classwall/"
        );

    } else {

        setcookie(
            "remembered_name",
            "",
            time() - 3600,
            "/Classwall/"
        );

    }

    // INSERT: Save message using a prepared statement
    $sql = "INSERT INTO messages (author, body)
            VALUES (:author, :body)";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ":author" => $author,
        ":body" => $body
    ]);

    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ClassWall</title>
</head>

<body>

    <h1>Welcome to ClassWall!</h1>
    <p>Share your thoughts with your classmates.</p>

    <!-- SESSION COUNTER -->
    <p>
        You've visited this page
        <?php echo $_SESSION["visits"]; ?>
        times.
    </p>

    <!-- POST FORM -->
    <form method="POST" action="">

        <label for="author">Enter Your Name:</label>
        <br>

        <input
            type="text"
            id="author"
            name="author"
            value="<?php echo htmlspecialchars($_COOKIE['remembered_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
            required
        >

        <br><br>

        <label for="body">Write Your Message:</label>
        <br>

        <textarea id="body" name="body" required></textarea>

        <br><br>

        <input type="checkbox" id="remember" name="remember">
        <label for="remember">Remember my name</label>

        <br><br>

        <button type="submit">Post Message</button>

    </form>

    <!-- GET SEARCH FORM -->
    <h2>Search Messages</h2>

    <form method="GET" action="">

        <input
            type="text"
            name="search"
            placeholder="Search messages..."
            value="<?php echo htmlspecialchars($_GET['search'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
        >

        <button type="submit">Search</button>

    </form>

    <!-- DISPLAY MESSAGES -->
    <h2>Class Messages</h2>

    <?php

    $search = trim($_GET["search"] ?? "");

    if ($search !== "") {

        // Search messages using a prepared statement
        $sql = "SELECT * FROM messages
                WHERE author ILIKE :search
                OR body ILIKE :search
                ORDER BY created_at DESC";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ":search" => "%" . $search . "%"
        ]);

    } else {

        // Display all messages, newest first
        $sql = "SELECT * FROM messages
                ORDER BY created_at DESC";

        $stmt = $pdo->query($sql);

    }

    // Display retrieved messages safely
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

        echo "<div>";

        echo "<h3>" . htmlspecialchars($row["author"], ENT_QUOTES, 'UTF-8') . "</h3>";

        echo "<p>" . htmlspecialchars($row["body"], ENT_QUOTES, 'UTF-8') . "</p>";

        echo "<small>" . htmlspecialchars($row["created_at"], ENT_QUOTES, 'UTF-8') . "</small>";

        echo "</div>";

    }

    ?>

</body>
</html>
