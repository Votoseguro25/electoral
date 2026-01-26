const departamentoSelect = document.getElementById('departamento')
const municipioSelect = document.getElementById('municipio')
const corregimientoSelect = document.getElementById('corregimiento')

const cargarMunicipios = async (departamento_id) => {
    try {
        const response = await fetch(`${window.location.origin}/electoral/departamentos/${departamento_id}/municipios/`)
            .then(response => response.json())

        return response
    } catch (error) {
        Swal.fire({
            title: "Error",
            text: "Algo falló al cargar los municipios, intenta más tarde.",
            icon: "error"
        });
    }
}

const cargarCorregimientos = async (municipio_id) => {
    try {
        const response = await fetch(`${window.location.origin}/electoral/municipios/${municipio_id}/corregimientos/`)
            .then(response => response.json())

        return response
    } catch (error) {
        Swal.fire({
            title: "Error",
            text: "Algo falló al cargar los corregimientos, intenta más tarde.",
            icon: "error"
        });
    }
}

const departamentoHandle = async () => {
    municipioSelect.innerHTML = '<option selected disabled>Municipio</option>'
    corregimientoSelect.innerHTML = '<option selected disabled>Corregimiento</option>'

    const departamento_id = departamentoSelect.value

    if (departamento_id) {
        const municipios = await cargarMunicipios(departamento_id)

        municipios.forEach(municipio => {
            const option = document.createElement('option')
            option.value = municipio.id
            option.textContent = municipio.nombre
            municipioSelect.appendChild(option)
        })

        // Restaurar valor si existe oldMunicipio
        if (typeof oldMunicipio !== 'undefined' && oldMunicipio !== -1) {
            municipioSelect.value = oldMunicipio
            await municipioHandle()
        }
    }
}

const municipioHandle = async () => {
    corregimientoSelect.innerHTML = '<option selected disabled>Corregimiento</option>'

    const municipio_id = municipioSelect.value

    if (municipio_id) {
        const corregimientos = await cargarCorregimientos(municipio_id)

        corregimientos.forEach(corregimiento => {
            const option = document.createElement('option')
            option.value = corregimiento.id
            option.textContent = corregimiento.nombre
            corregimientoSelect.appendChild(option)
        })

        // Restaurar valor si existe oldCorregimiento
        if (typeof oldCorregimiento !== 'undefined' && oldCorregimiento !== -1) {
            corregimientoSelect.value = oldCorregimiento
        }
    }
}

departamentoSelect?.addEventListener('change', departamentoHandle)
municipioSelect?.addEventListener('change', municipioHandle)

// Para modales de edición
document.addEventListener('DOMContentLoaded', () => {
    const departamentosEditar = document.querySelectorAll('.departamento-editar')
    const municipiosEditar = document.querySelectorAll('.municipio-editar')
    const corregimientosEditar = document.querySelectorAll('.corregimiento-editar')

    departamentosEditar.forEach(departamentoSelect => {
        departamentoSelect.addEventListener('change', async function () {
            const barrio_id = this.id.split('-')[1]
            const municipioSelect = document.getElementById(`municipio-${barrio_id}`)
            const corregimientoSelect = document.getElementById(`corregimiento-${barrio_id}`)

            municipioSelect.innerHTML = '<option selected disabled>Municipio</option>'
            corregimientoSelect.innerHTML = '<option selected disabled>Corregimiento</option>'

            const departamento_id = this.value

            if (departamento_id) {
                const municipios = await cargarMunicipios(departamento_id)

                municipios.forEach(municipio => {
                    const option = document.createElement('option')
                    option.value = municipio.id
                    option.textContent = municipio.nombre
                    municipioSelect.appendChild(option)
                })

                // Restaurar municipio si existe
                const municipioGuardado = municipioSelect.dataset.barrioMunicipio
                if (municipioGuardado) {
                    municipioSelect.value = municipioGuardado
                    municipioSelect.dispatchEvent(new Event('change'))
                }
            }
        })
    })

    municipiosEditar.forEach(municipioSelect => {
        municipioSelect.addEventListener('change', async function () {
            const barrio_id = this.id.split('-')[1]
            const corregimientoSelect = document.getElementById(`corregimiento-${barrio_id}`)

            corregimientoSelect.innerHTML = '<option selected disabled>Corregimiento</option>'

            const municipio_id = this.value

            if (municipio_id) {
                const corregimientos = await cargarCorregimientos(municipio_id)

                corregimientos.forEach(corregimiento => {
                    const option = document.createElement('option')
                    option.value = corregimiento.id
                    option.textContent = corregimiento.nombre
                    corregimientoSelect.appendChild(option)
                })

                // Restaurar corregimiento si existe
                const corregimientoGuardado = corregimientoSelect.dataset.barrioCorregimiento
                if (corregimientoGuardado) {
                    corregimientoSelect.value = corregimientoGuardado
                }
            }
        })
    })

    // Cargar datos iniciales para modales de edición
    document.querySelectorAll('.btn-modal-editar').forEach(btn => {
        btn.addEventListener('click', async function () {
            const barrio_id = this.id.split('-').pop()
            const departamentoSelect = document.getElementById(`departamento-${barrio_id}`)
            const municipioSelect = document.getElementById(`municipio-${barrio_id}`)

            if (departamentoSelect && departamentoSelect.value) {
                const departamento_id = departamentoSelect.value
                const municipios = await cargarMunicipios(departamento_id)

                municipioSelect.innerHTML = '<option selected disabled>Municipio</option>'
                municipios.forEach(municipio => {
                    const option = document.createElement('option')
                    option.value = municipio.id
                    option.textContent = municipio.nombre
                    municipioSelect.appendChild(option)
                })

                const municipioGuardado = municipioSelect.dataset.barrioMunicipio
                if (municipioGuardado) {
                    municipioSelect.value = municipioGuardado
                    
                    // Cargar corregimientos
                    const corregimientoSelect = document.getElementById(`corregimiento-${barrio_id}`)
                    const corregimientos = await cargarCorregimientos(municipioGuardado)

                    corregimientoSelect.innerHTML = '<option selected disabled>Corregimiento</option>'
                    corregimientos.forEach(corregimiento => {
                        const option = document.createElement('option')
                        option.value = corregimiento.id
                        option.textContent = corregimiento.nombre
                        corregimientoSelect.appendChild(option)
                    })

                    const corregimientoGuardado = corregimientoSelect.dataset.barrioCorregimiento
                    if (corregimientoGuardado) {
                        corregimientoSelect.value = corregimientoGuardado
                    }
                }
            }
        })
    })
})
