<?php include '../header.php'; 
if($_SESSION['user']['role']!='client') die("Only clients can post jobs");
if(isset($_POST['post'])){
    $title = $_POST['title'];
    $desc = $_POST['description'];
    $budget = $_POST['budget'];
    $cat = $_POST['category'];
    $image = '';
    if($_FILES['image']['name']){
        $image = time().'_'.$_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], '../uploads/'.$image);
    }
    mysqli_query($conn, "INSERT INTO jobs (title,description,budget,client_id,image,category) VALUES ('$title','$desc',$budget,".$_SESSION['user']['id'].",'$image','$cat')");
    echo "<p style='color:green;'>Job Posted Successfully!</p>";
}
?>
<div class="form-container">
    <h2>Post New Job</h2>
    <form method="POST" enctype="multipart/form-data">
        <input type="text" name="title" placeholder="Job Title" required><br><br>
        <textarea name="description" placeholder="Full Description" required></textarea><br><br>
        <input type="number" name="budget" placeholder="Budget (NPR)" required><br><br>
        <select name="category">
            <option>Web Development</option>
            <option>Graphic Design</option>
            <option>Mobile App</option>
            <option>Writing</option>
        </select><br><br>
        <input type="file" name="image" accept="image/*"><br><br>
        <button name="post">Post Job</button>
    </form>
</div>
<?php include '../footer.php'; ?>