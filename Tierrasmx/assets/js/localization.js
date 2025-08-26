function initializeLanguageToggle() {
    const languageToggles = document.querySelectorAll('.language-toggle');
    
    languageToggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            languageToggles.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            const lang = this.getAttribute('data-lang');
            showNotification(`Idioma cambiado a ${lang === 'es' ? 'Español' : 'English'}`, 'success');
        });
    });
}

const mexicanLocations = [
    { id: 'cdmx', name: 'Ciudad de México', type: 'estado' },
    { id: 'jalisco', name: 'Jalisco', type: 'estado' },
    { id: 'nuevo-leon', name: 'Nuevo León', type: 'estado' },
    { id: 'queretaro', name: 'Querétaro', type: 'estado' },
    { id: 'baja-california', name: 'Baja California', type: 'estado' },
    
    { id: 'cuauhtemoc', name: 'Cuauhtémoc', type: 'ciudad', parent: 'cdmx' },
    { id: 'miguel-hidalgo', name: 'Miguel Hidalgo', type: 'ciudad', parent: 'cdmx' },
    { id: 'benito-juarez', name: 'Benito Juárez', type: 'ciudad', parent: 'cdmx' },
    
    { id: 'guadalajara', name: 'Guadalajara', type: 'ciudad', parent: 'jalisco' },
    { id: 'zapopan', name: 'Zapopan', type: 'ciudad', parent: 'jalisco' },
    { id: 'tonala', name: 'Tonalá', type: 'ciudad', parent: 'jalisco' },
    
    { id: 'monterrey', name: 'Monterrey', type: 'ciudad', parent: 'nuevo-leon' },
    { id: 'san-nicolas', name: 'San Nicolás', type: 'ciudad', parent: 'nuevo-leon' },
    { id: 'santa-catarina', name: 'Santa Catarina', type: 'ciudad', parent: 'nuevo-leon' },
    
    { id: 'centro-historico', name: 'Centro Histórico', type: 'colonia', parent: 'cuauhtemoc' },
    { id: 'roma', name: 'Roma', type: 'colonia', parent: 'cuauhtemoc' },
    { id: 'condestancia', name: 'Condesa', type: 'colonia', parent: 'cuauhtemoc' },
    { id: 'polanco', name: 'Polanco', type: 'colonia', parent: 'miguel-hidalgo' },
    { id: 'lomas', name: 'Lomas de Chapultepec', type: 'colonia', parent: 'miguel-hidalgo' },
    
    { id: 'chapalita', name: 'Chapalita', type: 'colonia', parent: 'guadalajara' },
    { id: 'providencia', name: 'Providencia', type: 'colonia', parent: 'guadalajara' },
    { id: 'americana', name: 'Américana', type: 'colonia', parent: 'guadalajara' },
    { id: 'valle-rio', name: 'Valle del Río', type: 'colonia', parent: 'zapopan' },
    
    { id: 'valle-escalante', name: 'Valle Escalante', type: 'colonia', parent: 'monterrey' },
    { id: 'santa-catarina', name: 'Santa Catarina', type: 'colonia', parent: 'santa-catarina' },
    { id: 'san-pedro', name: 'San Pedro Garza García', type: 'colonia', parent: 'monterrey' }
];
