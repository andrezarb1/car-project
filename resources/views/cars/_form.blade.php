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
    <label class="form-label">Fuel Type *</label>
    <select name="fuel_type" class="form-select" required>
        <option value="">-- Select fuel type --</option>

        @php
            $options = [
                'petrol' => 'Petrol',
                'diesel' => 'Diesel',
                'hybrid' => 'Hybrid',
                'electric' => 'Electric',
            ];
            $current = old('fuel_type', $car->fuel_type ?? '');
        @endphp

        @foreach($options as $value => $label)
            <option value="{{ $value }}" {{ $current === $value ? 'selected' : '' }}>
                {{ $label }}
            </option>
        @endforeach
    </select>
</div>


    <div class="col-md-4 mb-3">
        <label class="form-label">VIN *</label>
        <input id="vinInput" type="text" name="vin" class="form-control" required
        value="{{ old('vin', $car->vin ?? '') }}">
        <div class="mt-2 d-flex gap-2">
        <button type="button" id="validateVinBtn" class="btn btn-outline-dark btn-sm">
            Validate VIN
        </button>
        <div id="vinStatus" class="small"></div>
</div>

    </div>

    <div class="mb-3">
    <label class="form-label">Car Images</label>
    <input type="file" name="images[]" class="form-control" multiple accept="image/*">
    <div class="form-text">You can upload multiple images (jpg/png/webp). Max 2MB each.
        
    </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('validateVinBtn');
    const vinInput = document.getElementById('vinInput');
    const vinStatus = document.getElementById('vinStatus');

    if (!btn || !vinInput || !vinStatus) return;

    btn.addEventListener('click', async () => {
        const vin = (vinInput.value || '').trim();

        vinStatus.textContent = '';
        btn.disabled = true;
        btn.textContent = 'Validating...';

        try {
            const res = await fetch("{{ url('/vin/validate') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Accept": "application/json"
                },
                body: JSON.stringify({ vin })
            });

            const data = await res.json();

            if (!res.ok) {
                vinStatus.innerHTML = `<span class="text-danger">${data.message || 'VIN validation failed.'}</span>`;
            } else {
                vinStatus.innerHTML = data.ok
                    ? `<span class="text-success">${data.message}</span>`
                    : `<span class="text-danger">${data.message}</span>`;
            }
        } catch (e) {
            vinStatus.innerHTML = `<span class="text-danger">Could not validate VIN (network/server error).</span>`;
        } finally {
            btn.disabled = false;
            btn.textContent = 'Validate VIN';
        }
    });
});
</script>

