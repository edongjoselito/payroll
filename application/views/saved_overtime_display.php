<h5>Saved Overtime</h5>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Personnel</th>
            <th>Date</th>
            <th>Hours</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($entries as $e): ?>
        <tr>
            <td><?= $e->first_name . ' ' . $e->last_name ?></td>
            <td><?= date('F d, Y', strtotime($e->date)) ?></td>
            <td><?= $e->hours ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
