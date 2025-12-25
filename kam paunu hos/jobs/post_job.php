<?php 
include '../header.php'; 

if($_SESSION['user']['role']!='client') {
    die("Only clients can post jobs");
}

if(isset($_POST['post'])){
    // Security: Sanitize inputs
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $desc = mysqli_real_escape_string($conn, $_POST['description']);
    $budget = mysqli_real_escape_string($conn, $_POST['budget']);
    $cat = mysqli_real_escape_string($conn, $_POST['category']);
    $client_id = $_SESSION['user']['id'];
    
    $image = 'default-job.jpg'; // Default image
    
    // Handle file upload
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0){
        $uploads_dir = '../uploads/';
        
        // Create uploads directory if it doesn't exist
        if(!is_dir($uploads_dir)){
            mkdir($uploads_dir, 0777, true);
        }
        
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $file_name = $_FILES['image']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $file_tmp = $_FILES['image']['tmp_name'];
        $file_size = $_FILES['image']['size'];
        
        // Validate file
        if(in_array($file_ext, $allowed_extensions)){
            if($file_size < 5000000){ // 5MB limit
                // Generate unique filename
                $image = time() . '_' . uniqid() . '.' . $file_ext;
                $destination = $uploads_dir . $image;
                
                if(move_uploaded_file($file_tmp, $destination)){
                    // Success - $image already set
                } else {
                    echo "<p style='color:red;'>Failed to upload image. Using default.</p>";
                    $image = 'default-job.jpg';
                }
            } else {
                echo "<p style='color:red;'>File too large. Max 5MB.</p>";
                $image = 'default-job.jpg';
            }
        } else {
            echo "<p style='color:red;'>Invalid file type. Only JPG, PNG, GIF allowed.</p>";
            $image = 'default-job.jpg';
        }
    }
    
    // Insert into database using prepared statement
    $query = "INSERT INTO jobs (title, description, budget, client_id, image, category) 
              VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ssdiss", $title, $desc, $budget, $client_id, $image, $cat);
    
    if(mysqli_stmt_execute($stmt)){
        echo "<div class='alert alert-success'>Job Posted Successfully!</div>";
    } else {
        echo "<div class='alert alert-danger'>Error: " . mysqli_error($conn) . "</div>";
    }
    mysqli_stmt_close($stmt);
}
?>

<div class="form-container">
    <h2>Post New Job</h2>
    <form method="POST" enctype="multipart/form-data">
        <input type="text" name="title" placeholder="Job Title" required maxlength="200"><br><br>
        <textarea name="description" placeholder="Full Description" required rows="5"></textarea><br><br>
        <input type="number" name="budget" placeholder="Budget (NPR)" required min="0" step="0.01"><br><br>
        <select name="category" required>
            <option value="">Select Category</option>
            <option value="Web Development">Web Development</option>
            <option value="Graphic Design">Graphic Design</option>
            <option value="Mobile App">Mobile App</option>
            <option value="Writing">Writing</option>
            <option value="Digital Marketing">Digital Marketing</option>
            <option value="Video Editing">Video Editing</option>
        </select><br><br>
        <label for="image">Job Image (Optional, Max 5MB):</label><br>
        <input type="file" name="image" id="image" accept="image/*"><br><br>
        <button type="submit" name="post" class="btn btn-primary">Post Job</button>
    </form>
</div>

<style>
    .form-container {
        max-width: 600px;
        margin: 30px auto;
        padding: 20px;
        background: #f9f9f9;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    
    .form-container input[type="text"],
    .form-container input[type="number"],
    .form-container select,
    .form-container textarea {
        width: 100%;
        padding: 10px;
        margin: 5px 0;
        border: 1px solid #ddd;
        border-radius: 5px;
        box-sizing: border-box;
    }
    
    .form-container textarea {
        resize: vertical;
    }
    
    .form-container button {
        background: #007bff;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 16px;
    }
    
    .form-container button:hover {
        background: #0056b3;
    }
    
    .alert {
        padding: 10px;
        margin: 10px 0;
        border-radius: 5px;
    }
    
    .alert-success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    .alert-danger {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
</style>

<?php include '../footer.php'; ?>