<!-- Modal -->
<div class="modal fade" id="calculatorModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered ">
        <div class="modal-content card-dark border border-dark text-dark">

            <div class="modal-header border-bottom border-dark">
                <h5 class="modal-title">Calculator</h5>
                <button type="button" class="btn ms-auto" data-bs-dismiss="modal" aria-label="Cerrar">
                    <i class="fas fa-xmark fa-lg text-dark"></i>
                </button>
            </div>

            <div class="modal-body">

                <!-- Display -->
                <input type="text" id="calc-display" class="form-control mb-3 text-end text-dark card-dark border border-dark" readonly>

                <!-- Buttons -->
                <div class="row g-2">

                    <!-- Row 1 -->
                    <div class="col-3"><button class="btn btn-secondary w-100" onclick="clearCalc()">C</button></div>
                    <div class="col-3"><button class="btn btn-secondary w-100" onclick="append('%')">%</button></div>
                    <div class="col-3"><button class="btn btn-secondary w-100" onclick="append('/')">÷</button></div>
                    <div class="col-3"><button class="btn btn-secondary w-100" onclick="append('*')">×</button></div>

                    <!-- Row 2 -->
                    <div class="col-3"><button class="btn btn-dark text-dark border border-dark w-100" onclick="append('7')">7</button></div>
                    <div class="col-3"><button class="btn btn-dark text-dark border border-dark w-100" onclick="append('8')">8</button></div>
                    <div class="col-3"><button class="btn btn-dark text-dark border border-dark w-100" onclick="append('9')">9</button></div>
                    <div class="col-3"><button class="btn btn-secondary w-100" onclick="append('-')">-</button></div>

                    <!-- Row 3 -->
                    <div class="col-3"><button class="btn btn-dark text-dark border border-dark w-100" onclick="append('4')">4</button></div>
                    <div class="col-3"><button class="btn btn-dark text-dark border border-dark w-100" onclick="append('5')">5</button></div>
                    <div class="col-3"><button class="btn btn-dark text-dark border border-dark w-100" onclick="append('6')">6</button></div>
                    <div class="col-3"><button class="btn btn-secondary w-100" onclick="append('+')">+</button></div>

                    <!-- Row 4 -->
                    <div class="col-3"><button class="btn btn-dark text-dark border border-dark w-100" onclick="append('1')">1</button></div>
                    <div class="col-3"><button class="btn btn-dark text-dark border border-dark w-100" onclick="append('2')">2</button></div>
                    <div class="col-3"><button class="btn btn-dark text-dark border border-dark w-100" onclick="append('3')">3</button></div>
                    <div class="col-3" rowspan="2">
                        <button class="btn btn-success w-100 h-100" onclick="calculate()">=</button>
                    </div>

                    <!-- Row 5 -->
                    <div class="col-6"><button class="btn btn-dark text-dark border border-dark w-100" onclick="append('0')">0</button></div>
                    <div class="col-3"><button class="btn btn-dark text-dark border border-dark w-100" onclick="append('.')">.</button></div>

                </div>

            </div>
        </div>
    </div>
</div>

<script>
    const display = document.getElementById('calc-display');

    function append(value) {
        display.value += value;
    }

    function clearCalc() {
        display.value = '';
    }

    function calculate() {
        try {
            display.value = eval(display.value);
        } catch {
            display.value = 'Error';
        }
    }
    document.getElementById('calculatorModal')
        .addEventListener('shown.bs.modal', () => {
            display.focus();
        });

    let isCalculatorOpen = false;

    const modal = document.getElementById('calculatorModal');

    // Detect open/close
    modal.addEventListener('shown.bs.modal', () => {
        isCalculatorOpen = true;
        display.focus();
    });

    modal.addEventListener('hidden.bs.modal', () => {
        isCalculatorOpen = false;
    });

    // Keyboard listener
    document.addEventListener('keydown', (e) => {

        if (!isCalculatorOpen) return;

        const key = e.key;

        // Numbers
        if (!isNaN(key)) {
            append(key);
        }

        // Operators
        if (['+', '-', '*', '/', '%', '.'].includes(key)) {
            append(key);
        }

        // Enter = calculate
        if (key === 'Enter') {
            e.preventDefault();
            calculate();
        }

        // Backspace
        if (key === 'Backspace') {
            e.preventDefault();
            display.value = display.value.slice(0, -1);
        }

        // Escape = clear
        if (key === 'Escape') {
            clearCalc();
        }
    });
</script>