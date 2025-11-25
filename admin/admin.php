<?php
// admin/admin.php
session_start();

/*
  Admin panel — single file:
  - Lists: Users, Categories, Listings, Collections, Transactions
  - Edit and Delete functionality (single-page form flow)
  - Assumptions:
    * $_SESSION['user'] contains the username (string)
    * Admin check is: username === 'aj_schulte' (change as needed)
    * MySQL credentials below should be edited to match your env
*/

// ---------------- CONFIG ----------------
$dbConfig = [
    'host' => '127.0.0.1',
    'user' => 'root',
    'pass' => '',
    'db'   => 'collectable_peddlers',
    'port' => 3306,
];

$ADMIN_USERNAMES = ['aj_schulte']; // change / extend or implement is_admin column

// ---------------- DB CONNECT ----------------
$mysqli = new mysqli($dbConfig['host'], $dbConfig['user'], $dbConfig['pass'], $dbConfig['db'], $dbConfig['port']);
if ($mysqli->connect_errno) {
    http_response_code(500);
    echo "DB connection failed: " . htmlspecialchars($mysqli->connect_error);
    exit;
}
$mysqli->set_charset('utf8mb4');

// ---------------- AUTH ----------------
$currentUser = $_SESSION['user'] ?? null;
if (!in_array($currentUser, $ADMIN_USERNAMES, true)) {
    http_response_code(403);
    echo "<p>Access denied. You must be an admin to view this page.</p>";
    echo "<p>Signed in as: " . htmlspecialchars($currentUser) . "</p>";
    exit;
}

// ---------------- CSRF ----------------
if (!isset($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
function check_csrf($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// ---------------- Helpers ----------------
function h($s){ return htmlspecialchars($s, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); }
function redirect($url){ header("Location: $url"); exit; }

// ---------------- ACTION HANDLING ----------------
// POST actions: update / delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $type   = $_POST['type']   ?? '';
    $token  = $_POST['csrf_token'] ?? '';
    if (!check_csrf($token)) {
        echo "<p>Invalid CSRF token</p>";
        exit;
    }

    // Update handlers
    if ($action === 'update') {
        if ($type === 'user') {
            $id = intval($_POST['user_id']);
            $username = $_POST['username'] ?? '';
            $email = $_POST['email'] ?? '';
            $name = $_POST['name'] ?? '';
            $phone = $_POST['phone_num'] ?? '';

            $stmt = $mysqli->prepare("UPDATE `user` SET username=?, email=?, name=?, phone_num=? WHERE user_id=?");
            $stmt->bind_param('ssssi', $username, $email, $name, $phone, $id);
            $stmt->execute();
            $stmt->close();
            redirect($_SERVER['REQUEST_URI']);
        }

        if ($type === 'category') {
            $id = intval($_POST['category_id']);
            $name = $_POST['name'] ?? '';
            $desc = $_POST['description'] ?? '';
            $stmt = $mysqli->prepare("UPDATE `category` SET name=?, description=? WHERE category_id=?");
            $stmt->bind_param('ssi', $name, $desc, $id);
            $stmt->execute();
            $stmt->close();
            redirect($_SERVER['REQUEST_URI']);
        }

        if ($type === 'listing') {
            $id = intval($_POST['listing_id']);
            $title = $_POST['title'] ?? '';
            $cond = $_POST['condition'] ?? 'Used';
            $desc = $_POST['description'] ?? '';
            // price comes like 250.00
            $price = floatval($_POST['price'] ?? 0);
            $image = $_POST['image_url'] ?? '';
            $status = $_POST['status'] ?? 'active';
            $stmt = $mysqli->prepare("UPDATE `listing` SET title=?, `condition`=?, description=?, price=?, image_url=?, status=? WHERE listing_id=?");
            $stmt->bind_param('sssds si', $title, $cond, $desc, $price, $image, $status, $id);
            // small compatibility for bind: do manual if above pattern fails
            $stmt->close();
            // fallback: run with simple prepared statement that binds correctly
            $stmt = $mysqli->prepare("UPDATE `listing` SET title=?, `condition`=?, description=?, price=?, image_url=?, status=? WHERE listing_id=?");
            $stmt->bind_param('sssdssi', $title, $cond, $desc, $price, $image, $status, $id);
            $stmt->execute();
            $stmt->close();
            redirect($_SERVER['REQUEST_URI']);
        }

        if ($type === 'collection') {
            $id = intval($_POST['collection_id']);
            $name = $_POST['name'] ?? '';
            $stmt = $mysqli->prepare("UPDATE `collection` SET name=? WHERE collection_id=?");
            $stmt->bind_param('si', $name, $id);
            $stmt->execute();
            $stmt->close();
            redirect($_SERVER['REQUEST_URI']);
        }

        if ($type === 'transaction') {
            $id = intval($_POST['transaction_id']);
            $status = $_POST['status'] ?? 'pending';
            $total_price = floatval($_POST['total_price'] ?? 0);
            $stmt = $mysqli->prepare("UPDATE `transaction` SET status=?, total_price=? WHERE transaction_id=?");
            $stmt->bind_param('sdi', $status, $total_price, $id);
            $stmt->execute();
            $stmt->close();
            redirect($_SERVER['REQUEST_URI']);
        }
    }

    // Delete handlers (use cascades where DB has them)
    if ($action === 'delete') {
        if ($type === 'user') {
            $id = intval($_POST['user_id']);
            $stmt = $mysqli->prepare("DELETE FROM `user` WHERE user_id=?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
            redirect($_SERVER['REQUEST_URI']);
        }
        if ($type === 'category') {
            $id = intval($_POST['category_id']);
            $stmt = $mysqli->prepare("DELETE FROM `category` WHERE category_id=?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
            redirect($_SERVER['REQUEST_URI']);
        }
        if ($type === 'listing') {
            $id = intval($_POST['listing_id']);
            $stmt = $mysqli->prepare("DELETE FROM `listing` WHERE listing_id=?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
            redirect($_SERVER['REQUEST_URI']);
        }
        if ($type === 'collection') {
            $id = intval($_POST['collection_id']);
            $stmt = $mysqli->prepare("DELETE FROM `collection` WHERE collection_id=?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
            redirect($_SERVER['REQUEST_URI']);
        }
        if ($type === 'transaction') {
            $id = intval($_POST['transaction_id']);
            $stmt = $mysqli->prepare("DELETE FROM `transaction` WHERE transaction_id=?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
            redirect($_SERVER['REQUEST_URI']);
        }
    }
}

// ---------------- FETCH DATA ----------------
function fetch_all($mysqli, $sql) {
    $res = $mysqli->query($sql);
    $rows = [];
    if ($res) {
        while ($r = $res->fetch_assoc()) $rows[] = $r;
        $res->free();
    }
    return $rows;
}

$users = fetch_all($mysqli, "SELECT user_id, username, email, name, phone_num FROM `user` ORDER BY user_id");
$categories = fetch_all($mysqli, "SELECT category_id, name, description FROM `category` ORDER BY category_id");
$listings = fetch_all($mysqli, "SELECT listing_id, user_id, title, `condition`, description, price, image_url, created_at, status FROM `listing` ORDER BY listing_id");
$collections = fetch_all($mysqli, "SELECT collection_id, user_id, name, created_at FROM `collection` ORDER BY collection_id");
$collection_listings = fetch_all($mysqli, "SELECT * FROM `collection_listing` ORDER BY collection_id, listing_id");
$transactions = fetch_all($mysqli, "SELECT transaction_id, buyer_id, seller_id, transaction_date, total_price, status FROM `transaction` ORDER BY transaction_id");
$transaction_listings = fetch_all($mysqli, "SELECT * FROM `transaction_listing` ORDER BY transaction_id, listing_id");

// ---------------- UI ----------------
?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Admin — Collectable Peddlers</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    body{font-family:system-ui,Segoe UI,Roboto,Helvetica,Arial;margin:18px;background:#f7f7f8;color:#111}
    h1,h2{margin:6px 0}
    table{width:100%;border-collapse:collapse;margin:8px 0 24px;background:#fff}
    th,td{padding:8px;border:1px solid #e3e3e3;text-align:left;font-size:14px}
    .small{font-size:13px;color:#555}
    .btn{padding:6px 8px;border-radius:6px;text-decoration:none;border:1px solid #888;background:#fff;color:#111}
    .danger{background:#ffecec;border-color:#ffb3b3}
    .success{background:#ecffe7;border-color:#b3ffbf}
    form.inline{display:inline}
    .wrap{max-width:1100px;margin:0 auto}
    .row{display:grid;grid-template-columns:1fr 1fr;gap:18px}
    textarea{width:100%;min-height:64px}
    input[type="text"], input[type="number"]{width:100%}
    .meta{font-size:12px;color:#666}
  </style>
</head>
<body>
  <div class="wrap">
    <header>
      <h1>Admin Dashboard</h1>
      <p class="small">Signed in as <strong><?=h($currentUser)?></strong> — CSRF token: <code><?=h($_SESSION['csrf_token'])?></code></p>
      <nav><a href="../" class="btn">Back to site</a></nav>
    </header>

    <section>
      <h2>Users</h2>
      <table>
        <thead><tr><th>ID</th><th>Username</th><th>Email</th><th>Name</th><th>Phone</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach($users as $u): ?>
          <tr>
            <td><?=h($u['user_id'])?></td>
            <td><?=h($u['username'])?></td>
            <td><?=h($u['email'])?></td>
            <td><?=h($u['name'])?></td>
            <td><?=h($u['phone_num'])?></td>
            <td>
              <form class="inline" method="get" action="">
                <input type="hidden" name="edit" value="user">
                <input type="hidden" name="id" value="<?=h($u['user_id'])?>">
                <button class="btn">Edit</button>
              </form>
              <form class="inline" method="post" action="" onsubmit="return confirm('Delete user <?=h($u['username'])?>? This cascades listings and collections.');">
                <input type="hidden" name="csrf_token" value="<?=h($_SESSION['csrf_token'])?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="type" value="user">
                <input type="hidden" name="user_id" value="<?=h($u['user_id'])?>">
                <button class="btn danger">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </section>

    <section class="row">
      <div>
        <h2>Categories</h2>
        <table>
          <thead><tr><th>ID</th><th>Name</th><th>Description</th><th>Actions</th></tr></thead>
          <tbody>
          <?php foreach($categories as $c): ?>
            <tr>
              <td><?=h($c['category_id'])?></td>
              <td><?=h($c['name'])?></td>
              <td><?=h($c['description'])?></td>
              <td>
                <form class="inline" method="get" action="">
                  <input type="hidden" name="edit" value="category">
                  <input type="hidden" name="id" value="<?=h($c['category_id'])?>">
                  <button class="btn">Edit</button>
                </form>
                <form class="inline" method="post" action="" onsubmit="return confirm('Delete category <?=h($c['name'])?>?');">
                  <input type="hidden" name="csrf_token" value="<?=h($_SESSION['csrf_token'])?>">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="type" value="category">
                  <input type="hidden" name="category_id" value="<?=h($c['category_id'])?>">
                  <button class="btn danger">Delete</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <div>
        <h2>Listings</h2>
        <table>
          <thead><tr><th>ID</th><th>Title</th><th>Seller (user_id)</th><th>Price</th><th>Status</th><th>Actions</th></tr></thead>
          <tbody>
            <?php foreach($listings as $l): ?>
              <tr>
                <td><?=h($l['listing_id'])?></td>
                <td><?=h($l['title'])?></td>
                <td class="meta"><?=h($l['user_id'])?></td>
                <td><?=number_format((float)$l['price'], 2)?></td>
                <td><?=h($l['status'])?></td>
                <td>
                  <form class="inline" method="get" action="">
                    <input type="hidden" name="edit" value="listing">
                    <input type="hidden" name="id" value="<?=h($l['listing_id'])?>">
                    <button class="btn">Edit</button>
                  </form>
                  <form class="inline" method="post" action="" onsubmit="return confirm('Delete listing <?=h($l['title'])?>?');">
                    <input type="hidden" name="csrf_token" value="<?=h($_SESSION['csrf_token'])?>">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="type" value="listing">
                    <input type="hidden" name="listing_id" value="<?=h($l['listing_id'])?>">
                    <button class="btn danger">Delete</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </section>

    <section>
      <h2>Collections</h2>
      <table>
        <thead><tr><th>ID</th><th>User ID</th><th>Name</th><th>Created</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach($collections as $c): ?>
            <tr>
              <td><?=h($c['collection_id'])?></td>
              <td><?=h($c['user_id'])?></td>
              <td><?=h($c['name'])?></td>
              <td class="meta"><?=h($c['created_at'])?></td>
              <td>
                <form class="inline" method="get" action="">
                  <input type="hidden" name="edit" value="collection">
                  <input type="hidden" name="id" value="<?=h($c['collection_id'])?>">
                  <button class="btn">Edit</button>
                </form>
                <form class="inline" method="post" action="" onsubmit="return confirm('Delete collection <?=h($c['name'])?>?');">
                  <input type="hidden" name="csrf_token" value="<?=h($_SESSION['csrf_token'])?>">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="type" value="collection">
                  <input type="hidden" name="collection_id" value="<?=h($c['collection_id'])?>">
                  <button class="btn danger">Delete</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <h3>Collection - Listings mapping</h3>
      <table>
        <thead><tr><th>Collection ID</th><th>Listing ID</th><th>Added At</th></tr></thead>
        <tbody>
          <?php foreach($collection_listings as $cl): ?>
            <tr>
              <td><?=h($cl['collection_id'])?></td>
              <td><?=h($cl['listing_id'])?></td>
              <td><?=h($cl['added_at'])?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </section>

    <section>
      <h2>Transactions</h2>
      <table>
        <thead><tr><th>ID</th><th>Buyer</th><th>Seller</th><th>Date</th><th>Total</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach($transactions as $t): ?>
            <tr>
              <td><?=h($t['transaction_id'])?></td>
              <td><?=h($t['buyer_id'])?></td>
              <td><?=h($t['seller_id'])?></td>
              <td class="meta"><?=h($t['transaction_date'])?></td>
              <td><?=number_format((float)$t['total_price'],2)?></td>
              <td><?=h($t['status'])?></td>
              <td>
                <form class="inline" method="get" action="">
                  <input type="hidden" name="edit" value="transaction">
                  <input type="hidden" name="id" value="<?=h($t['transaction_id'])?>">
                  <button class="btn">Edit</button>
                </form>
                <form class="inline" method="post" action="" onsubmit="return confirm('Delete transaction <?=h($t['transaction_id'])?>?');">
                  <input type="hidden" name="csrf_token" value="<?=h($_SESSION['csrf_token'])?>">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="type" value="transaction">
                  <input type="hidden" name="transaction_id" value="<?=h($t['transaction_id'])?>">
                  <button class="btn danger">Delete</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <h3>Transaction - Listings mapping</h3>
      <table>
        <thead><tr><th>Transaction ID</th><th>Listing ID</th><th>Quantity</th><th>Price at sale</th></tr></thead>
        <tbody>
          <?php foreach($transaction_listings as $tl): ?>
            <tr>
              <td><?=h($tl['transaction_id'])?></td>
              <td><?=h($tl['listing_id'])?></td>
              <td><?=h($tl['quantity'])?></td>
              <td><?=h($tl['price_at_sale'])?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </section>

    <!-- ---------- EDIT FORMS (rendered when ?edit=type&id=NN) ---------- -->
    <?php
      $edit = $_GET['edit'] ?? null;
      $editId = intval($_GET['id'] ?? 0);
      if ($edit && $editId):
        if ($edit === 'user'):
          $stmt = $mysqli->prepare("SELECT user_id, username, email, name, phone_num FROM `user` WHERE user_id=?");
          $stmt->bind_param('i', $editId); $stmt->execute();
          $res = $stmt->get_result(); $row = $res->fetch_assoc(); $stmt->close();
          if ($row):
    ?>
      <section>
        <h2>Edit User #<?=h($row['user_id'])?></h2>
        <form method="post" action="">
          <input type="hidden" name="csrf_token" value="<?=h($_SESSION['csrf_token'])?>">
          <input type="hidden" name="action" value="update">
          <input type="hidden" name="type" value="user">
          <input type="hidden" name="user_id" value="<?=h($row['user_id'])?>">
          <label>Username<br><input type="text" name="username" value="<?=h($row['username'])?>"></label><br>
          <label>Email<br><input type="text" name="email" value="<?=h($row['email'])?>"></label><br>
          <label>Name<br><input type="text" name="name" value="<?=h($row['name'])?>"></label><br>
          <label>Phone<br><input type="text" name="phone_num" value="<?=h($row['phone_num'])?>"></label><br><br>
          <button class="btn success">Save</button>
        </form>
      </section>
    <?php
          endif;
        endif;

        if ($edit === 'category'):
          $stmt = $mysqli->prepare("SELECT category_id, name, description FROM `category` WHERE category_id=?");
          $stmt->bind_param('i', $editId); $stmt->execute();
          $res = $stmt->get_result(); $row = $res->fetch_assoc(); $stmt->close();
          if ($row):
    ?>
      <section>
        <h2>Edit Category #<?=h($row['category_id'])?></h2>
        <form method="post" action="">
          <input type="hidden" name="csrf_token" value="<?=h($_SESSION['csrf_token'])?>">
          <input type="hidden" name="action" value="update">
          <input type="hidden" name="type" value="category">
          <input type="hidden" name="category_id" value="<?=h($row['category_id'])?>">
          <label>Name<br><input type="text" name="name" value="<?=h($row['name'])?>"></label><br>
          <label>Description<br><textarea name="description"><?=h($row['description'])?></textarea></label><br>
          <button class="btn success">Save</button>
        </form>
      </section>
    <?php
          endif;
        endif;

        if ($edit === 'listing'):
          $stmt = $mysqli->prepare("SELECT listing_id, user_id, title, `condition`, description, price, image_url, status FROM `listing` WHERE listing_id=?");
          $stmt->bind_param('i', $editId); $stmt->execute();
          $res = $stmt->get_result(); $row = $res->fetch_assoc(); $stmt->close();
          if ($row):
    ?>
      <section>
        <h2>Edit Listing #<?=h($row['listing_id'])?></h2>
        <form method="post" action="">
          <input type="hidden" name="csrf_token" value="<?=h($_SESSION['csrf_token'])?>">
          <input type="hidden" name="action" value="update">
          <input type="hidden" name="type" value="listing">
          <input type="hidden" name="listing_id" value="<?=h($row['listing_id'])?>">
          <label>Title<br><input type="text" name="title" value="<?=h($row['title'])?>"></label><br>
          <label>Condition<br><input type="text" name="condition" value="<?=h($row['condition'])?>"></label><br>
          <label>Description<br><textarea name="description"><?=h($row['description'])?></textarea></label><br>
          <label>Price (e.g. 250.00)<br><input type="text" name="price" value="<?=h($row['price'])?>"></label><br>
          <label>Image URL<br><input type="text" name="image_url" value="<?=h($row['image_url'])?>"></label><br>
          <label>Status<br>
            <select name="status">
              <option value="active" <?= $row['status']==='active' ? 'selected' : '' ?>>active</option>
              <option value="sold" <?= $row['status']==='sold' ? 'selected' : '' ?>>sold</option>
              <option value="archived" <?= $row['status']==='archived' ? 'selected' : '' ?>>archived</option>
            </select>
          </label><br><br>
          <button class="btn success">Save listing</button>
        </form>
      </section>
    <?php
          endif;
        endif;

        if ($edit === 'collection'):
          $stmt = $mysqli->prepare("SELECT collection_id, user_id, name FROM `collection` WHERE collection_id=?");
          $stmt->bind_param('i', $editId); $stmt->execute();
          $res = $stmt->get_result(); $row = $res->fetch_assoc(); $stmt->close();
          if ($row):
    ?>
      <section>
        <h2>Edit Collection #<?=h($row['collection_id'])?></h2>
        <form method="post" action="">
          <input type="hidden" name="csrf_token" value="<?=h($_SESSION['csrf_token'])?>">
          <input type="hidden" name="action" value="update">
          <input type="hidden" name="type" value="collection">
          <input type="hidden" name="collection_id" value="<?=h($row['collection_id'])?>">
          <label>Name<br><input type="text" name="name" value="<?=h($row['name'])?>"></label><br><br>
          <button class="btn success">Save collection</button>
        </form>
      </section>
    <?php
          endif;
        endif;

        if ($edit === 'transaction'):
          $stmt = $mysqli->prepare("SELECT transaction_id, buyer_id, seller_id, total_price, status FROM `transaction` WHERE transaction_id=?");
          $stmt->bind_param('i', $editId); $stmt->execute();
          $res = $stmt->get_result(); $row = $res->fetch_assoc(); $stmt->close();
          if ($row):
    ?>
      <section>
        <h2>Edit Transaction #<?=h($row['transaction_id'])?></h2>
        <form method="post" action="">
          <input type="hidden" name="csrf_token" value="<?=h($_SESSION['csrf_token'])?>">
          <input type="hidden" name="action" value="update">
          <input type="hidden" name="type" value="transaction">
          <input type="hidden" name="transaction_id" value="<?=h($row['transaction_id'])?>">
          <label>Total Price<br><input type="text" name="total_price" value="<?=h($row['total_price'])?>"></label><br>
          <label>Status<br>
            <select name="status">
              <option value="pending" <?= $row['status']==='pending' ? 'selected' : '' ?>>pending</option>
              <option value="completed" <?= $row['status']==='completed' ? 'selected' : '' ?>>completed</option>
              <option value="canceled" <?= $row['status']==='canceled' ? 'selected' : '' ?>>canceled</option>
            </select>
          </label><br><br>
          <button class="btn success">Save transaction</button>
        </form>
      </section>
    <?php
          endif;
        endif;
      endif;
    ?>
  </div>
</body>
</html>
