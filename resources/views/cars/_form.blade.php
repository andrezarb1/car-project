<div class="mb-3">
    <label class="form-label">Dealer *</label>
    <select name="dealer_id" class="form-select" required>
        <option value="">-- Select dealer --</option>
        @foreach($dealers as $dealer)
            <option value="{{ $dealer->id }}"
                {{ (string)old('dealer_id', $car->dealer_id ?? '') === (string)$dealer->id ? 'selected' : '' }}>
                {{ $dealer->name }}
            </option>
        @endforeach
    </select>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label">Make *</label>
        <input type="text" name="make" class="form-control" required
               value="{{ old('make', $car->make ?? '') }}">
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label">Model *</label>
        <input type="text" name="model" class="form-control" required
               value="{{ old('model', $car->model ?? '') }}">
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label">Year *</label>
        <input type="number" name="year" class="form-control" required
               value="{{ old('year', $car->year ?? '') }}">
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label">Price (€) *</label>
        <input type="number" step="0.01" name="price" class="form-control" required
               value="{{ old('price', $car->price ?? '') }}">
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label">Fuel Type</label>
        <input type="text" name="fuel_type" class="form-control"
               value="{{ old('fuel_type', $car->fuel_type ?? '') }}" placeholder="Petrol / Diesel / Hybrid / Electric">
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label">VIN *</label>
        <input type="text" name="vin" class="form-control" required
               value="{{ old('vin', $car->vin ?? '') }}">
        <div class="form-text">We’ll validate VIN via external API later.</div>
    </div>
</div>
