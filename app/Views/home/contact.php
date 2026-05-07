<div class="hero py-5"><div class="container text-center"><h1 class="display-5 fw-bold">Contact Us</h1></div></div>
<section class="py-5"><div class="container">
<div class="row justify-content-center"><div class="col-md-6">
    <form method="POST" action="/contact" class="bg-white p-4 rounded shadow-sm">
        <?= csrf_field() ?>
        <div class="mb-3"><label class="form-label">Name</label><input type="text" name="name" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">Subject</label><input type="text" name="subject" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">Message</label><textarea name="message" rows="5" class="form-control" required></textarea></div>
        <button type="submit" class="btn btn-primary w-100">Send Message</button>
    </form>
</div></div>
</div></section>
