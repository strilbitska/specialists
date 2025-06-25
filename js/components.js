function loadComponent(elementId, componentPath) {
    console.log('Starting to load component...', {elementId, componentPath});
    
    const element = document.getElementById(elementId);
    if (!element) {
        console.error('Element not found:', elementId);
        return;
    }
    
    fetch(componentPath)
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.text();
        })
        .then(data => {
            console.log('Data received, length:', data.length);
            element.innerHTML = data;
            console.log('Component loaded successfully');
        })
        .catch(error => {
            console.error('Error loading component:', error);
            element.innerHTML = 'Error loading component';
        });
}