function cargarNotificaciones() {
    fetch('../php/obtener_notificaciones.php')
        .then(res => res.json())
        .then(data => {
            const count = data.length;
            document.getElementById('notificationCount').innerText = count;
            const list = document.getElementById('notificationList');
            list.innerHTML = '';
            if (count === 0) {
                list.innerHTML = '<li>No hay eventos próximos</li>';
            } else {
                data.forEach(ev => {
                    let item = document.createElement('li');
                    item.innerHTML = `<strong>${ev.titulo}</strong> - ${new Date(ev.fecha).toLocaleDateString()}`;
                    list.appendChild(item);
                });
            }
        });
}
setInterval(cargarNotificaciones, 60000); // cada minuto
cargarNotificaciones();