<table class="table table-bordered">
    <thead>
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Address</th>
        <th>Actions</th>
    </tr>
    </thead>
    <tbody>
    <?php while ($row = $foodBanks->fetch_assoc()): ?>
        <tr>
            <td><?= $row['food_bank_id'] ?></td>
            <td><?= $row['name'] ?></td>
            <td><?= $row['address'] ?></td>
            <td>
                <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editFoodBankModal<?= $row['food_bank_id'] ?>">Edit</button>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="food_bank_id" value="<?= $row['food_bank_id'] ?>">
                    <button class="btn btn-danger btn-sm" name="delete_food_bank">Delete</button>
                </form>
            </td>
        </tr>
    <?php endwhile; ?>
    </tbody>
</table>

<!-- Add Modal -->
<!-- Edit Modal -->
