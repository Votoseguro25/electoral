const municipios = []
let municipioSeleccionado

const departamentoSelect = document.getElementById('departamento')
const municipioSelect = document.getElementById('municipio')
const puestoSelect = document.getElementById('puesto')
const mesaSelect = document.getElementById('mesa')

const cargarMunicipios = async (departamento_id) => {
    try {
        const response = await fetch(`./departamentos/${departamento_id}/municipios/`)
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

const departamentoHandle = async () => {
    const selectedDepartamento = departamentoSelect.value

    municipioSelect.options.length = 1
    puestoSelect.options.length = 1
    mesaSelect.options.length = 1

    if (isNaN(parseInt(selectedDepartamento))) return

    if (!municipios.some(municipio => municipio.departamento_id == selectedDepartamento)) {
        municipios.push(...await cargarMunicipios(selectedDepartamento))
    }

    municipios.filter(municipio => municipio.departamento_id == selectedDepartamento).forEach(municipio => {
        const municipioOption = document.createElement('option')

        municipioOption.value = municipio.id
        municipioOption.textContent = municipio.nombre

        if (typeof oldMunicipio !== 'undefined' && oldMunicipio == municipio.id) municipioOption.selected = true

        municipioSelect.appendChild(municipioOption)
    })

    municipioHandle()
}

const municipioHandle = async (event) => {
    puestoSelect.options.length = 1
    mesaSelect.options.length = 1

    municipioSeleccionado = municipios.find(municipio => municipio.id == municipioSelect.value)

    if (!municipioSeleccionado || !municipioSeleccionado.puestos || !municipioSeleccionado.puestos.length) return

    for (const puesto of municipioSeleccionado.puestos) {
        const puestoOption = document.createElement('option')
        puestoOption.value = puesto.id
        puestoOption.textContent = puesto.nombre

        if (typeof oldPuesto !== 'undefined' && oldPuesto == puesto.id) puestoOption.selected = true

        puestoSelect.appendChild(puestoOption)
    }
    
    puestoHandle()
}

const puestoHandle = (event) => {
    const selectedPuesto = puestoSelect.value

    mesaSelect.options.length = 1

    if (!municipioSeleccionado || !municipioSeleccionado.puestos || !municipioSeleccionado.puestos.length) return

    const puesto = municipioSeleccionado.puestos.find(e => e.id == selectedPuesto)

    if (!puesto || !puesto.mesas || !puesto.mesas.length) return

    for (const mesa of puesto.mesas) {
        const option = document.createElement('option')

        option.value = mesa.id
        option.textContent = mesa.descripcion

        if (typeof oldMesa !== 'undefined' && oldMesa == mesa.id) option.selected = true

        mesaSelect.appendChild(option)
    }
}

departamentoSelect.addEventListener('input', departamentoHandle)
municipioSelect.addEventListener('input', municipioHandle)
puestoSelect.addEventListener('input', puestoHandle)

//ejecutarlos por si hubo algun error de digitación
departamentoHandle()
municipioHandle()
puestoHandle()

const botonesEditar = [...document.querySelectorAll('button.btn-modal-editar')]
const departamentosEditar = [...document.querySelectorAll('select.departamento-editar')]
const municipiosEditar = [...document.querySelectorAll('select.municipio-editar')]
const puestosEditar = [...document.querySelectorAll('select.puesto-editar')]
const mesasEditar = [...document.querySelectorAll('select.mesa-editar')]

const departamentoHandleEditar = async ({ departamento_id, votante_id }) => {
    const municipioEditarSelect = document.getElementById(`municipio-${votante_id}`)
    municipioEditarSelect.options.length = 1

    if (isNaN(parseInt(departamento_id))) return

    if (!municipios.some(municipio => municipio.departamento_id == departamento_id)) {
        municipios.push(...await cargarMunicipios(departamento_id))
    }

    municipios.filter(municipio => municipio.departamento_id == departamento_id).forEach(municipio => {
        const municipioOption = document.createElement('option')

        municipioOption.value = municipio.id
        municipioOption.textContent = municipio.nombre

        if (municipioEditarSelect.dataset.votanteMunicipio == municipio.id) {

            municipioOption.selected = true

            municipioHandleEditar({
                municipio: municipio,
                votante_id
            })
        }

        municipioEditarSelect.appendChild(municipioOption)
    })
}

const municipioHandleEditar = ({ municipio, votante_id }) => {
    const puestoEditarSelect = document.getElementById(`puesto-${votante_id}`)
    puestoEditarSelect.options.length = 1

    if (!municipio || !municipio.puestos || !municipio.puestos.length) return

    for (const puesto of municipio.puestos) {
        const puestoOption = document.createElement('option')
        puestoOption.value = puesto.id
        puestoOption.textContent = puesto.nombre

        if (puestoEditarSelect.dataset.votantePuesto == puesto.id) {
            puestoOption.selected = true

            puestoHandleEditar({
                puesto,
                votante_id
            })
        }

        puestoEditarSelect.appendChild(puestoOption)
    }
}

const puestoHandleEditar = ({ puesto, votante_id }) => {
    const mesaEditarSelect = document.getElementById(`mesa-${votante_id}`)
    mesaEditarSelect.options.length = 1

    if (!puesto || !puesto.mesas.length) return

    for (const mesa of puesto.mesas) {
        const option = document.createElement('option')

        option.value = mesa.id
        option.textContent = mesa.descripcion

        if (mesaEditarSelect.dataset.votanteMesa == mesa.id) option.selected = true

        mesaEditarSelect.appendChild(option)
    }
}

for (const botonEditar of botonesEditar) {
    botonEditar.addEventListener('click', () => {

        const votante_id = botonEditar.id.split("-")[3]

        departamentoHandleEditar({
            departamento_id: document.getElementById(`departamento-${votante_id}`).value,
            votante_id
        })
    })
}

for (const departamentoEditar of departamentosEditar) {
    departamentoEditar.addEventListener('input', async (e) => {

        const votante_id = departamentoEditar.id.split("-")[1]

        await departamentoHandleEditar({
            departamento_id: e.target.value,
            votante_id
        })

        const municipio = municipios.find(municipio => document.getElementById(`municipio-${votante_id}`).value == municipio.id)

        if (municipio) {
            municipioHandleEditar({
                municipio,
                votante_id
            })

            const puesto = municipio.puestos?.find(puesto => document.getElementById(`puesto-${votante_id}`).value == puesto.id)

            if (puesto) {
                puestoHandleEditar({
                    votante_id,
                    puesto
                })
            }
        }
    })
}

for (const municipioEditar of municipiosEditar) {
    municipioEditar.addEventListener('input', (e) => {
        const votante_id = municipioEditar.id.split("-")[1]

        const municipio = municipios.find(municipio => municipio.id == e.target.value)

        if (municipio) {
            municipioHandleEditar({
                municipio,
                votante_id
            })

            const puesto = municipio.puestos?.find(puesto => document.getElementById(`puesto-${votante_id}`).value == puesto.id)

            if (puesto) {
                puestoHandleEditar({
                    votante_id,
                    puesto
                })
            }
        }
    })
}

for (const puestoEditar of puestosEditar) {
    puestoEditar.addEventListener('input', (e) => {
        const votante_id = puestoEditar.id.split("-")[1]

        const municipio = municipios.find(municipio => municipio.id == document.getElementById(`municipio-${votante_id}`).value)
        
        const puesto = municipio?.puestos?.find(puesto => puesto.id == e.target.value)
        
        if (puesto) {
            puestoHandleEditar({ votante_id, puesto })
        }
    })
}

const departamentoBuscadorSelect = document.querySelector('#buscador select[name="departamento"]')
const municipioBuscadorSelect = document.querySelector('#buscador select[name="municipio"]')

const cargarMunicipiosBuscador = async (departamento_id) => {
    try {
        const response = await fetch(`./departamentos/${departamento_id}/municipios/`)
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

const departamentoBuscadorHandle = async () => {
    const selectedDepartamento = departamentoBuscadorSelect.value

    municipioBuscadorSelect.options.length = 1

    if (isNaN(parseInt(selectedDepartamento))) return

    if (!municipios.some(municipio => municipio.departamento_id == selectedDepartamento)) {
        municipios.push(...await cargarMunicipiosBuscador(selectedDepartamento))
    }

    municipios.filter(municipio => municipio.departamento_id == selectedDepartamento).forEach(municipio => {
        const municipioOption = document.createElement('option')

        municipioOption.value = municipio.id
        municipioOption.textContent = municipio.nombre

        if (municipioBuscadorSelect.dataset.municipioSeleccionado == municipio.id) {
            municipioOption.selected = true
        }

        municipioBuscadorSelect.appendChild(municipioOption)
    })
}

if (departamentoBuscadorSelect) {
    departamentoBuscadorSelect.addEventListener('input', departamentoBuscadorHandle)
    departamentoBuscadorHandle()
}