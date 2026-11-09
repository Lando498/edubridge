const connectionStatus = document.querySelector('#connection-status');

async function checkBackendConnection() {
	try {
		const response = await fetch('Backend/index.php?action=health', {
			headers: { Accept: 'application/json' }
		});
		const result = await response.json();

		if (!response.ok || result.ok !== true) {
			throw new Error('Backend health check failed.');
		}

		connectionStatus.textContent = 'Backend connected';
		connectionStatus.dataset.state = 'connected';
	} catch {
		connectionStatus.textContent = 'Backend unavailable';
		connectionStatus.dataset.state = 'unavailable';
	}
}

checkBackendConnection();
