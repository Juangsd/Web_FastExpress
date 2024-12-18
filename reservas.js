document.getElementById('descargar-boleto').addEventListener('click', () => {
    const nombre = document.getElementById('nombre').value;
    const dni = document.getElementById('dni').value;
    const origen = document.getElementById('origen').value;
    const destino = document.getElementById('destino').value;
    const fecha = document.getElementById('fecha').value;
    const cantidad = document.getElementById('cantidad').value;
    const metodoPago = document.getElementById('pago').value;
    const asientos = Array.from(document.getElementById('asientos').selectedOptions).map(option => option.value).join(', ');

    // Rellenar los datos del boleto
    document.getElementById('boleto-nombre').textContent = nombre;
    document.getElementById('boleto-dni').textContent = dni;
    document.getElementById('boleto-origen').textContent = origen;
    document.getElementById('boleto-destino').textContent = destino;
    document.getElementById('boleto-fecha').textContent = fecha;
    document.getElementById('boleto-cantidad').textContent = cantidad;
    document.getElementById('boleto-asientos').textContent = asientos;
    document.getElementById('boleto-pago').textContent = metodoPago;

    document.getElementById('boleto').style.display = 'block';

    html2canvas(document.getElementById('boleto')).then(canvas => {
        const link = document.createElement('a');
        link.download = 'boleto.png';
        link.href = canvas.toDataURL();
        link.click();
        document.getElementById('boleto').style.display = 'none';
    });
});

