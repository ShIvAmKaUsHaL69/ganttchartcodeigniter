<?php $this->load->view('layout/header'); ?>
<div class="d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <form method="post" class="border p-4 rounded" style="min-width: 280px;">
        <h4 class="mb-3 text-center">Enter Password</h4>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger py-1 small"><?= $error; ?></div>
        <?php endif; ?>
        <div class="form-group">
            <input type="password" name="password" class="form-control" placeholder="Password" autofocus required>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Unlock</button>
    </form>
</div>
<?php $this->load->view('layout/footer'); ?> 