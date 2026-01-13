<div class="mb-3">
    <label class="form-label">Name *</label>
    <input type="text" name="name" class="form-control"
           value="{{ old('name', $dealer->name ?? '') }}" required>
</div>

<div class="mb-3">
    <label class="form-label">Location *</label>
    <input type="text" name="location" class="form-control" required
    value="{{ old('location', $dealer->location ?? '') }}">

<div class="mb-3">
    <label class="form-label">Email *</label>
    <input type="email" name="email" class="form-control" required
           value="{{ old('email', $dealer->email ?? '') }}">
</div>
