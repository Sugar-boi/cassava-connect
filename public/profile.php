<?php
require_once __DIR__ . '/../includes/header.php';
require_login();
verify_csrf();

$user_id = current_user_id();
$stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $name = clean_input($_POST['name']);
    $phone = clean_input($_POST['phone']);
    $password = $_POST['password'] ?? '';
    $imagePath = $user['profile_image'];

    if(isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK){
        $tmp = $_FILES['profile_image']['tmp_name'];
        $fname = time().'_'.preg_replace('/[^a-zA-Z0-9._-]/','',$_FILES['profile_image']['name']);
        $dest = __DIR__ . '/../uploads/profiles/' . $fname;
        if(move_uploaded_file($tmp, $dest)){
            $imagePath = '/cassava-connect/uploads/profiles/' . $fname;
        }
    }

    if($password){
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE users SET full_name=?, phone=?, password=?, profile_image=? WHERE id=?")
            ->execute([$name, $phone, $hash, $imagePath, $user_id]);
    } else {
        $pdo->prepare("UPDATE users SET full_name=?, phone=?, profile_image=? WHERE id=?")
            ->execute([$name, $phone, $imagePath, $user_id]);
    }

    flash_set('Profile updated.');
    header('Location: profile.php');
    exit;
}
?>

<h2>My Profile</h2>
<form method="post" enctype="multipart/form-data">
  <input type="hidden" name="csrf_token" value="<?=csrf_token()?>">
  <div class="mb-3">
    <label>Name</label>
    <input name="name" class="form-control" value="<?=htmlspecialchars($user['full_name'])?>">
  </div>
  <div class="mb-3">
    <label>Phone</label>
    <input name="phone" class="form-control" value="<?=htmlspecialchars($user['phone'])?>">
  </div>
  <div class="mb-3">
    <label>New Password</label>
    <input type="password" name="password" class="form-control" placeholder="Leave blank to keep current">
  </div>
  <div class="mb-3">
    <label>Profile Image</label><br>
    <?php if($user['profile_image']): ?>
      <img src="<?=$user['profile_image']?>" width="80" class="rounded mb-2">
    <?php endif; ?>
    <input type="file" name="profile_image" class="form-control">
  </div>
  <button class="btn btn-success">Update Profile</button>
</form>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
