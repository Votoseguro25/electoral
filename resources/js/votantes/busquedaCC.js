const peticion = async (url) => {
    return await (await fetch(url, { method: 'GET' })).json()
}

cedula.addEventListener('change', async () => {
    nombre.value = ''

    const previousPlaceholder = ({ menssage, time }) => {
        nombre.placeholder = menssage

        setTimeout(() => {
            nombre.placeholder = "Nombre completo"
        }, time ?? 3000);
    }

    try {
        const apiURL = 'http://localhost:3000'
        const respuesta = await peticion(`${apiURL}/?CC=${cedula.value}`)

        if (respuesta.encontrado) return nombre.value = `${respuesta.persona.nombres} ${respuesta.persona.apellidos}`

        if (!respuesta.encontrado) previousPlaceholder({ menssage: 'No encontrado', time: 3000 })
    } catch (error) {
        previousPlaceholder({ menssage: 'Error al buscar, intenta más tarde', time: 3000 })
    }

    nombre.disabled = false
})