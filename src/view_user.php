<?php
// Include database connection
require_once 'includes/config.php';

// Fetch all users
$users = [];
$sql = "SELECT id, first_name, location, email, phone, registered_at, last_login FROM users WHERE id != 1";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $users = $result->fetch_all(MYSQLI_ASSOC);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once 'includes/config.php';

    $user_id = $_POST['user_id'] ?? null;
    $new_password = $_POST['new_password'] ?? '';

    if ($user_id && !empty($new_password)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("si", $hashed_password, $user_id);
            if ($stmt->execute()) {
                echo "<p class='text-green-500'>Password updated successfully.</p>";
            }
            $stmt->close();
        } 
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Reporters</title>
    <link href="./output.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            padding-top: 5rem; /* Add body padding for fixed navbar */
        }
        main {
            padding-top: 1rem; /* Additional spacing for content */
        }
    </style>
</head>
<body class="bg-black min-h-screen text-white">
 <?php include 'includes/navbar.php'; ?>
 <div class="mb-6">
            <a href="admin_dashboard.php" class="text-blue-500 hover:underline">
                <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
            </a>
        </div>
    <div class="lg:col-span-2 px-4">
        <div class="bg-gray-900 rounded-lg border border-gray-800 p-6 overflow-x-auto">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold text-white">Reporters</h2>
            </div>

            <?php if (!empty($users)): ?>
                <table class="min-w-full bg-gray-900">
                    <thead>
                       <tr class="bg-gray-800">
                            <th class="py-2 px-4 text-left text-gray-300">Name</th>
							<th class="py-2 px-4 text-left text-gray-300">Location</th>
                            <th class="py-2 px-4 text-left text-gray-300">Email</th>
                            <th class="py-2 px-4 text-left text-gray-300">Phone</th>
							<th class="py-2 px-4 text-left text-gray-300">Last Login</th>
							<th class="py-2 px-4 text-left text-gray-300">Registered At</th>
							<th class="py-2 px-4 text-left text-gray-300"> New Password</th>
							<th class="py-2 px-4 text-left text-gray-300">Action</th>
							

                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr class="border-t border-gray-800 resource-row">
                                <td class="py-3 px-4"><?= htmlspecialchars($user['first_name']) ?></td>
								<td class="py-3 px-4"><?= htmlspecialchars($user['location']) ?></td>
                                <td class="py-3 px-4"><?= htmlspecialchars($user['email']) ?></td>
                                <td class="py-3 px-4"><?= htmlspecialchars($user['phone']) ?></td>
								<td class="py-3 px-4"><?= htmlspecialchars($user['last_login']) ?></td>
								<td class="py-3 px-4"><?= htmlspecialchars($user['registered_at']) ?></td>
								<td class="py-3 px-4">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="flex space-x-2 items-center">
				<input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                    <input type="password" id="new_password" name="new_password" required class="w-full px-4 py-2 rounded-lg bg-gray-800 border border-gray-700 text-white focus:outline-none focus:border-blue-500">
            </td>
            <td class="py-3 px-4">
                    <button 
                        type="submit" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-1 rounded"
                    >
                        Save
                    </button>
                </form>
            </td>
                           

                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-gray-400">No users found.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
