fetch('noticias.json')
  .then(response => response.json())
  .then(noticias => {
    const contenedor = document.getElementById('noticias');
    noticias.forEach(noticia => {
      const div = document.createElement('div');
      div.classList.add('noticia');
      div.innerHTML = `
        <h4>${noticia.titulo}</h4>
        <small>${noticia.fecha}</small>
        <p>${noticia.mensaje}</p>
      `;
      contenedor.appendChild(div);
    });
  })
  .catch(error => {
    console.error('Error cargando noticias:', error);
  });
