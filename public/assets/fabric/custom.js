var canvas;
var activeObject;
function initCanvas(element) {
    if (!canvas) {
        canvas = new fabric.Canvas('canvas');
    }
    canvas.setDimensions({
        width: element.width(),
        height: 600
    });

    canvas.setBackgroundColor('#565656', canvas.renderAll.bind(canvas));
    canvas.on('selection:updated', updateInfo);
    canvas.on('selection:created', updateInfo);
    canvas.on('selection:cleared', updateInfo);

    canvas.on('mouse:down', function (opt) {
        var evt = opt.e;
        if (evt.altKey === true) {
            this.isDragging = true;
            this.selection = false;
            this.lastPosX = evt.clientX;
            this.lastPosY = evt.clientY;
        }
    });
    canvas.on('mouse:move', function (opt) {
        if (this.isDragging) {
            var e = opt.e;
            var vpt = this.viewportTransform;
            vpt[4] += e.clientX - this.lastPosX;
            vpt[5] += e.clientY - this.lastPosY;
            this.requestRenderAll();
            this.lastPosX = e.clientX;
            this.lastPosY = e.clientY;
        }
    });
    canvas.on('mouse:up', function (opt) {
        // on mouse up we want to recalculate new interaction
        // for all objects, so we call setViewportTransform
        this.setViewportTransform(this.viewportTransform);
        this.isDragging = false;
        this.selection = true;
    });
    canvas.on('mouse:wheel', function (opt) {
        var evt = opt.e;
        if (evt.altKey === true)
        {
            var delta = opt.e.deltaY;
            var zoom = canvas.getZoom();
            zoom *= 0.999 ** delta;
            if (zoom > 20) zoom = 20;
            if (zoom < 0.01) zoom = 0.01;
            canvas.zoomToPoint({ x: opt.e.offsetX, y: opt.e.offsetY }, zoom);
            opt.e.preventDefault();
            opt.e.stopPropagation();
        }
    });


}

initCanvas($('#editor-canvas'));

// create a rect object

function addRect() {
    var rect = new fabric.Rect({
        left: canvas.width / 2,
        top: canvas.height / 2,
        fill: 'transparent',
        width: 100,
        height: 100,
        originX: 'center',
        originY: 'center',
        stroke: '#ff0000',
        strokeWidth: 3,
        strokeUniform: true,
    });
    canvas.add(rect);
    canvas.setActiveObject(rect);
}

function addCircle() {
    var circl = new fabric.Circle({
        left: canvas.width / 2,
        top: canvas.height / 2,
        fill: 'transparent',
        radius: 50,
        originX: 'center',
        originY: 'center',
        stroke: '#ff0000',
        strokeWidth: 3,
        strokeUniform: true,
    });
    canvas.add(circl);
    canvas.setActiveObject(circl);
}

function addArrow() {
    var triangle = new fabric.Triangle({
        width: 10,
        height: 15,
        fill: 'red',
        left: 235,
        top: 65,
        angle: 90
    });

    var line = new fabric.Line([50, 100, 200, 100], {
        left: 75,
        top: 70,
        stroke: 'red'
    });

    var objs = [line, triangle];

    var alltogetherObj = new fabric.Group(objs);
    alltogetherObj.set('fill', '#ff0000');
    canvas.add(alltogetherObj);
    canvas.setActiveObject(alltogetherObj);
}

function deleteObject(eventData, transform) {
    var target = transform.target;
    var canvas = target.canvas;
    canvas.remove(target);
    canvas.requestRenderAll();
}

function renderIcon(ctx, left, top, styleOverride, fabricObject) {
    var size = this.cornerSize;
    ctx.save();
    ctx.translate(left, top);
    ctx.rotate(fabric.util.degreesToRadians(fabricObject.angle));
    ctx.drawImage(img, -size / 2, -size / 2, size, size);
    ctx.restore();
}

function Import(data) {
    canvas.loadFromJSON(data, function () {
        canvas.renderAll();
        reRender();
    });
}

function addText() {
    let text = new fabric.IText('Text', {
        left: canvas.width / 2,
        top: canvas.height / 2,
        fill: '#000000',
        fontFamily: 'sans-serif',
        hasRotatingPoint: false,
        centerTransform: true,
        originX: 'center',
        originY: 'center',
        lockUniScaling: true
    });

    canvas.add(text);
    canvas.setActiveObject(text);
}

function addImage() {
    $('#addImageModal').modal('show');
}

function reRender() {
    backgroundImage = canvas.backgroundImage;
    var current_width = $("#editor-canvas").width();
    var current_height = $("#editor-canvas").height();
    if (backgroundImage) {
        let width = backgroundImage.getScaledWidth();
        let height = backgroundImage.getScaledHeight();
        canvas.setZoom(1);
        canvas.setDimensions({
            width: width,
            height: height
        });
        canvas.set();
    }
    else {
        canvas.setDimensions({
            width: $("#editor-canvas").width(),
            height: 600
        });
    }

    var outerCanvasContainer = $("#editor-canvas");

    var ratio          = canvas.getWidth() / canvas.getHeight();
    var containerWidth = outerCanvasContainer.width();
    var scale          = containerWidth / canvas.getWidth();
    var zoom           = canvas.getZoom() * scale;

    canvas.setDimensions({width: containerWidth, height: containerWidth / ratio});
    canvas.setViewportTransform([zoom, 0, 0, zoom, 0, 0]);
}

function remove() {
    let activeObjects = canvas.getActiveObjects();
    canvas.discardActiveObject();
    if (activeObjects.length) {
        canvas.remove.apply(canvas, activeObjects);
    }
}

function updateInfo() {
    activeObject = canvas.getActiveObject();
    if (activeObject) {
        color = activeObject.get('fill');
        if (activeObject.type == 'image') {
            $('.change-color').addClass('d-none');
            $('.change-border-color').addClass('d-none');
        }
        else if (activeObject.type == 'i-text') {
            $('.change-color').removeClass('d-none');
            $('.change-border-color').addClass('d-none');
        }
        else if (activeObject.type == 'circle' || activeObject.type == 'rect') {
            $('.change-color').removeClass('d-none');
            $('.change-border-color').removeClass('d-none');

            var border = activeObject.get('stroke');
            $('input[name="fill_border_color"]').val(border);
            $('#border-color-fill').text(border);
        }
        else {
            $('.change-color').removeClass('d-none');
            $('.change-border-color').addClass('d-none');
        }

        $('input[name="fill_color"]').val(color);
        $('#color-fill').text(color);
        $('#editPosition').removeClass('d-none');
        $('.selector-remove-button').removeClass('d-none');
    }
    else {
        $('#editPosition').addClass('d-none');
        $('.selector-remove-button').addClass('d-none');
        $('#editColor').addClass('d-none');
    }

}

function loadPagination(totalPage = 0, index = 0)
{
    $('.next-button').removeClass('d-none');
    $('.previous-button').removeClass('d-none');

    $('.index-page-canvas').text(index + 1);
    $('.total-page-canvas').text(totalPage);


    if (!totalPage || !index || totalPage == 1 || index == 0) {
        $('.previous-button').addClass('d-none');
    }

    if (!totalPage || index + 1 == totalPage) {
        $('.next-button').addClass('d-none');
    }
}

function sendToBack() {
    activeObject = canvas.getActiveObject();
    if (activeObject) {
        activeObject.sendToBack();
        return true;
    }

    return false
}

function sendBackwards() {
    activeObject = canvas.getActiveObject();
    if (activeObject) {
        activeObject.sendBackwards();
        return true;
    }

    return false
}

function bringToFront() {
    activeObject = canvas.getActiveObject();
    if (activeObject) {
        activeObject.bringToFront();
        return true;
    }

    return false
}

function bringForward() {
    activeObject = canvas.getActiveObject();
    if (activeObject) {
        activeObject.bringForward();
        return true;
    }

    return false
}

$(document).ready(function () {
    $('.import-image').on('click', function () {
        var image = $('input[name="add_image"]').val();
        fabric.Image.fromURL(image, (img) => {
            canvas.setBackgroundImage(img, canvas.renderAll.bind(canvas), {
                scaleX: canvas.width / img.width,
                scaleY: canvas.width / img.width
            });
            reRender();
        });
        $('input[name="add_image"]').val('');
        $('#addImageModal').modal('hide');
    })

    $('input[name="fill_color"]').change(function () {
        activeObject = canvas.getActiveObject();
        if (activeObject) {
            activeObject.set('fill', $(this).val());
            $('#color-fill').text($(this).val());
            if (typeof activeObject.getObjects === "function") {
                objects = activeObject.getObjects();
                objects.forEach(element => {
                    element.set('fill', $(this).val());
                    element.set('stroke', $(this).val());
                });
            }

            canvas.requestRenderAll();
        }
        canvas.renderAll();
    });

    $('input[name="fill_border_color"]').change(function () {
        activeObject = canvas.getActiveObject();
        if (activeObject) {
            activeObject.set('stroke', $(this).val());
            $('#border-color-fill').text($(this).val());
            canvas.requestRenderAll();
        }
        canvas.renderAll();
    });

    // canvas.setDimensions({
    //     width: $("#editor-canvas").width(),
    //     height: 600
    // });
});