const cedula = document.getElementById('cedula')
const nombre = document.getElementById('nombre')
const telefono = document.getElementById('telefono')
const municipio = document.getElementById('municipio')
const corregimiento = document.getElementById('corregimiento')
const barrio = document.getElementById('barrio')
const puesto = document.getElementById('puesto')
const mesa = document.getElementById('mesa')
const compromiso = document.getElementById('compromiso')
const recomendacion = document.getElementById('recomendacion')
const formularioAgregar = document.getElementById('formulario-agregar')

const insertarMensaje = ({ elemento, mensaje }) => {
    const inputGroup = elemento.parentNode

    const textoAnterior = inputGroup.nextElementSibling

    if (textoAnterior) textoAnterior.remove()

    const span = document.createElement('span')
    span.classList.add('text-danger')
    span.classList.add('mt-2')
    span.classList.add('error-message')

    span.textContent = mensaje

    inputGroup.after(span)
}

const removerMensaje = (elemento) => {
    const inputGroup = elemento.parentNode
    const texto = inputGroup.nextElementSibling

    if (texto) texto.remove()
}

const cedulaValidacion = (event) => {
    const input = event.target
    const valor = event.target.value

    removerMensaje(input)

    if (!valor.length) {
        insertarMensaje({
            elemento: input,
            mensaje: 'No puedes dejar este campo vacío'
        })
        return true
    }

    if (valor.length > 255) {
        insertarMensaje({
            elemento: input,
            mensaje: 'No puedes superar los 255 caracteres'
        })
        return true
    }
}

const nombreValidacion = (event) => {
    const input = event.target
    const valor = event.target.value

    removerMensaje(input)

    if (!valor.length) {
        insertarMensaje({
            elemento: input,
            mensaje: 'No puedes dejar este campo vacío'
        })
        return true
    }

    if (valor.length > 255) {
        insertarMensaje({
            elemento: input,
            mensaje: 'No puedes superar los 255 caracteres'
        })
        return true
    }
}

const telefonoValidacion = (event) => {
    const input = event.target
    const valor = event.target.value

    removerMensaje(input)

    if (!valor.length) {
        insertarMensaje({
            elemento: input,
            mensaje: 'No puedes dejar este campo vacío'
        })
        return true
    }

    if (valor.length > 10) {
        insertarMensaje({
            elemento: input,
            mensaje: 'El número de telefono no debe exceder los 10 digitos'
        })
        return true
    }
}

const municipioValidacion = (event) => {
    const input = event.target
    const valor = event.target.value

    removerMensaje(input)

    if (Number.isNaN(Number.parseInt(valor))) {
        insertarMensaje({
            elemento: input,
            mensaje: 'Selecciona un elemento de la lista'
        })
        return true
    }
}

const corregimientoValidacion = (event) => {
    const input = event.target
    const valor = event.target.value

    removerMensaje(input)

    if (!valor) {
        insertarMensaje({
            mensaje: 'No puedes dejar este campo vacío',
            elemento: input
        })
        return true
    }

    if (isNaN(parseInt(valor))) {
        insertarMensaje({
            elemento: input,
            mensaje: 'Selecciona un elemento de la lista'
        })
        return true
    }
}

const barrioValidacion = (event) => {
    const input = event.target
    const valor = event.target.value

    removerMensaje(input)

    if (!valor) {
        insertarMensaje({
            mensaje: 'No puedes dejar este campo vacío',
            elemento: input
        })
        return true
    }

    if (isNaN(parseInt(valor))) {
        insertarMensaje({
            elemento: input,
            mensaje: 'Selecciona un elemento de la lista'
        })
        return true
    }
}

const puestoValidacion = (event) => {
    const input = event.target
    const valor = event.target.value

    removerMensaje(input)

    if (!valor) {
        insertarMensaje({
            mensaje: 'No puedes dejar este campo vacío',
            elemento: input
        })
        return true
    }

    if (isNaN(parseInt(valor))) {
        insertarMensaje({
            elemento: input,
            mensaje: 'Selecciona un elemento de la lista'
        })
        return true
    }
}

const mesaValidacion = (event) => {
    const input = event.target
    const valor = event.target.value

    removerMensaje(input)

    if (!valor) {
        insertarMensaje({
            mensaje: 'No puedes dejar este campo vacío',
            elemento: input
        })
        return true
    }

    if (isNaN(parseInt(valor))) {
        insertarMensaje({
            elemento: input,
            mensaje: 'Selecciona un elemento de la lista'
        })
        return true
    }
}

const compromisoValidacion = (event) => {
    const input = event.target
    const valor = event.target.value
    removerMensaje(input)

    if (!valor) {
        insertarMensaje({
            mensaje: 'No puedes dejar este campo vacío',
            elemento: input
        })
        return true
    }

    if (isNaN(parseInt(valor))) {
        insertarMensaje({
            elemento: input,
            mensaje: 'Selecciona un elemento de la lista'
        })
        return true
    }
}

const recomendacionValidacion = (event) => {
    const input = event.target
    const valor = event.target.value

    removerMensaje(input)

    if (valor.length > 255) {
        insertarMensaje({
            elemento: input,
            mensaje: 'No puedes superar los 255 caracteres'
        })
        return true
    }
}


cedula.addEventListener('change', cedulaValidacion)
nombre.addEventListener('change', nombreValidacion)
telefono.addEventListener('change', telefonoValidacion)
municipio.addEventListener('change', municipioValidacion)
corregimiento.addEventListener('change', corregimientoValidacion)
barrio.addEventListener('change', barrioValidacion)
puesto.addEventListener('change', puestoValidacion)
mesa.addEventListener('change', mesaValidacion)
compromiso.addEventListener('change', compromisoValidacion)
recomendacion.addEventListener('change', recomendacionValidacion)

formularioAgregar.addEventListener('submit', (event) => {
    let hayErrores = cedulaValidacion(event = { target: cedula }) || nombreValidacion(event = { target: nombre }) || telefonoValidacion(event = { target: telefono }) || municipioValidacion(event = { target: municipio }) || corregimientoValidacion(event = { target: corregimiento }) || barrioValidacion(event = { target: barrio }) || puestoValidacion(event = { target: puesto }) || mesaValidacion(event = { target: mesa }) || compromisoValidacion(event = { target: compromiso }) || recomendacionValidacion(event = { target: recomendacion })

    if (hayErrores) {
        event.preventDefault
        return
    }

    formularioAgregar.submit()
})