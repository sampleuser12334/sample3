<?php
session_start();
include "db_conn2.php";
$host = 'localhost';
$dbname = 'labcode';
$user = 'root';
$pass = '';
// Create a PDO instance
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
// Fetch categories from the database
$categoriesQuery = $pdo->query("SELECT DISTINCT eqp_categ FROM lab_eqpment");
$categories = $categoriesQuery->fetchAll(PDO::FETCH_COLUMN);

// Fetch subcategories from the database
$subcategoriesQuery = $pdo->query("SELECT DISTINCT eqp_categ, eqp_name, eqp_size FROM lab_eqpment");
$subcategories = array();
while ($row = $subcategoriesQuery->fetch(PDO::FETCH_ASSOC)) {
    $subcategories[$row['eqp_categ']][$row['eqp_name']][] = $row['eqp_size'];
}
$db = new mysqli($host, $user, $pass, $dbname);  
// Check connection  
if ($db->connect_error) {  
    die("Connection failed: " . $db->connect_error);  
}
$result = $db->query("SELECT img_eqp FROM lab_eqpdetails"); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style_borrow.css">
    <title>LabCode</title>
</head>
<body>
	<form action="borrow_process.php" method="post">
		<?php
		
		?>
		
		<?php if (isset($_SESSION["displayed_studName"])) { ?>
		<?php	$studName = $_SESSION["displayed_studName"]; ?>
			<h2 class="welc"><?php echo "Welcome, " . htmlspecialchars($studName)."!";?></h2>
		<?php } ?> 
		<?php if (isset($_GET['success'])) { ?>
        <p class="succ"><?php echo $_GET['success']; ?> </p>
		<?php } ?>
		<?php if(isset($_GET['error'])) { ?>
			<p class="error"><?php echo $_GET['error']; ?>
		</p>
			<?php } ?>
		<center>
		<h3>Laboratory Equipment</h3>
		<!-- Display images with BLOB data from database -->
		<?php if($result->num_rows > 0){ ?> 
			<div class="gallery"> 
				<?php while($row = $result->fetch_assoc()){ ?> 
					<img src="data:image/jpg;charset=utf8;base64,<?php echo base64_encode($row['img_eqp']); ?>" /> 
				<?php } ?> 
			</div> 
		<?php }else{ ?> 
			<p class="status error">Image(s) not found...</p> 
		<?php } ?>
		<label for="category">Select Category:</label><br>
		<select name="category" id="category" class="dropdown">
			<?php
			foreach ($categories as $category) {
				echo '<option value="' . $category . '">' . ucfirst($category) . '</option>';
			}
			?>
		</select>
		<br>
		<label for="subcategory">Select Item:</label><br>
		<select name="subcategory" id="subcategory" class="dropdown"></select>
		<br>
		<!-- New column dropdown -->
		<label for="new-column">Select Size:</label><br>
		<select name="new-column" id="new-column" class="dropdown"></select>
		<br>
		<br>
		<br>
		<script>
			document.addEventListener('DOMContentLoaded', function() {
				// Declare selectedCategory outside the event listener
				let selectedCategory;

				// JavaScript to dynamically update subcategories and new column options when the category changes
				document.getElementById('category').addEventListener('change', function() {
					selectedCategory = this.value;
					const subcategoryDropdown = document.getElementById('subcategory');

					// Clear existing options
					subcategoryDropdown.innerHTML = '';

					// Populate subcategories dropdown based on selected category
					<?php foreach ($categories as $category): ?>
						<?php $subcategoriesForCategory = isset($subcategories[$category]) ? $subcategories[$category] : array(); ?>
						if (selectedCategory === '<?php echo $category; ?>') {
							<?php foreach ($subcategoriesForCategory as $subcategory => $nestedSubcategories): ?>
								createOption(subcategoryDropdown, '<?php echo $subcategory; ?>');
							<?php endforeach; ?>
						}
					<?php endforeach; ?>
				});

				// JavaScript to dynamically update new column options when the subcategory changes
				document.getElementById('subcategory').addEventListener('change', function() {
					const selectedSubcategory = this.value;
					const newColumnDropdown = document.getElementById('new-column');

					// Clear existing options
					newColumnDropdown.innerHTML = '';

					// Populate new column dropdown based on selected subcategory
					<?php foreach ($categories as $category): ?>
						<?php $subcategoriesForCategory = isset($subcategories[$category]) ? $subcategories[$category] : array(); ?>
						<?php foreach ($subcategoriesForCategory as $subcategory => $nestedSubcategories): ?>
							if (selectedCategory === '<?php echo $category; ?>' && selectedSubcategory === '<?php echo $subcategory; ?>') {
								<?php foreach ($nestedSubcategories as $nestedSubcategory): ?>
									createOption(newColumnDropdown, '<?php echo $nestedSubcategory; ?>');
								<?php endforeach; ?>
							}
						<?php endforeach; ?>
					<?php endforeach; ?>
				});

				// Function to create and append an option element
				function createOption(selectElement, text) {
					const option = document.createElement('option');
					option.textContent = text;
					selectElement.appendChild(option);
				}
			});
		</script>
		<label>Assisted by:</label>
		<br>
		<select class="assist">	
			<?php 
				$categories = mysqli_query($conn, "SELECT * from assistant");
				while($c = mysqli_fetch_array($categories)) {
			?>
			<option value=""><?php echo $c['name'] ?></option>
				<?php } ?>
		</select>
		<br>
		<br>
		<br>
		<button type="submit" name="btn" class="log">Borrow</button>
		<br>
		<a href="login_ui.php" id="reg">Logout</a>
	</form>
	
</body>
</html>