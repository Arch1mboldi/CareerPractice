<?php
include 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nickname = $_POST['nickname'];
    $codeblock = $_POST['codeblock'];
    $comment = $_POST['comment'];

    $stmt = $pdo->prepare('INSERT INTO posts (nickname, codeblock, comment, created_at) VALUES (?, ?, ?, NOW())');
    $stmt->execute([$nickname, $codeblock, $comment]);

    header('Location: forum.php');
    exit();
}

if (isset($_GET['upvote'])) {
    $post_id = (int)$_GET['upvote'];
    $user_ip = $_SERVER['REMOTE_ADDR'];

    $stmt = $pdo->prepare('SELECT * FROM upvotes WHERE post_id = ? AND user_ip = ?');
    $stmt->execute([$post_id, $user_ip]);
    $upvote = $stmt->fetch();

    if (!$upvote) {
        $stmt = $pdo->prepare('INSERT INTO upvotes (post_id, user_ip) VALUES (?, ?)');
        $stmt->execute([$post_id, $user_ip]);

        $stmt = $pdo->prepare('UPDATE posts SET upvotes = upvotes + 1 WHERE id = ?');
        $stmt->execute([$post_id]);
    }

    header('Location: forum.php');
    exit();
}

$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

switch ($sort) {
    case 'upvotes':
        $query = 'SELECT * FROM posts ORDER BY upvotes DESC';
        break;
    case 'oldest':
        $query = 'SELECT * FROM posts ORDER BY created_at ASC';
        break;
    case 'newest':
    default:
        $query = 'SELECT * FROM posts ORDER BY created_at DESC';
        break;
}
$posts = $pdo->query($query)->fetchAll();

$comments_count_query = $pdo->query('SELECT COUNT(*) FROM posts');
$comments_count = $comments_count_query->fetchColumn();

?>
<!DOCTYPE html>
<html lang = zh_CN>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ecust-C-Instruction</title>
    <link rel="stylesheet" href="css/global.css" />
    <link rel="stylesheet" href="css/forum.css">
    <title>Forum</title>
</head>
<body>
    <header>
        <div class="标题">
            <h1>华东理工大学C语言实验指导</h1>
            <img src="images/华理校徽.png" alt="华理校徽" height="80px">
            <img src="images/C语言logo.png" alt="C语言" height="80px">
        </div>
        <nav>
            <ul>
                <li><a href="index.html">主界面</a></li>
                <li><a href="concepts.html">概念解释</a></li>
                <li><a href="instructions.html">实验指导</a></li>
                <li><a href="forum.php">讨论板</a></li>
            </ul>
        </nav>
    </header>


    <div class="留言区">
        <form method="POST" action="">
            <label for="nickname">昵称:</label><br>
            <input type="text" id="nickname" name="nickname" required><br>
            <label for="codeblock">代码块:</label><br>
            <textarea id="codeblock" name="codeblock" required></textarea><br>
            <label for="comment">留言:</label><br>
            <textarea id="comment" name="comment" required></textarea><br>
            <input type="submit" value="Submit">
        </form>
    </div>

<div class = "sort">
    <form method="GET" action="">
    <p>当前总评论数：<?php echo $comments_count; ?></p>
    <h3>评论排列方式</h3>
    <button type="submit" name="sort" value="upvotes">最多赞同</button>
    <button type="submit" name="sort" value="newest">最新</button>
    <button type="submit" name="sort" value="oldest">最老</button>
    </form>
</div>

<?php foreach ($posts as $post): ?>
    <div class = comments>

        <h3>用户<?php echo htmlspecialchars($post['nickname']); ?></h3>
        <br>
        <pre><?php echo htmlspecialchars($post['codeblock']); ?></pre>
        <p><?php echo htmlspecialchars($post['comment']); ?></p>
        <br>
        <p>评论时间<?php echo htmlspecialchars($post['created_at']); ?></p>
        <p>赞同 <?php echo $post['upvotes']; ?></p>
        
        
        <form method="GET" action="">
            <input type="hidden" name="upvote" value="<?php echo $post['id']; ?>">
            <input type="submit" value="赞同👍">
        </form>
    </div>
    <hr>
<?php endforeach; ?>
</body>
</html>