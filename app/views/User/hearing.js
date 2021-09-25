/* Internal constants */
const noteTypePiano = "1/2 (Piano) Tonos reales ";
const noteTypeSin = "2/2 (Puros) Tonos artificiales ";
const unselectedNote = "Sin seleccionar";

/* Internal variables*/
var timeCurrent = 0;
var isDone = false;
var isRunning = false;
var shouldRestart = false;
var timeStart = Date.now();
var timeReference = Date.now();
var reactionTime = 0;
var audiosFetched = 0;
var audiosInMemory = [];
var audiosSources = JSON.parse("%audios_sources%");
var viewSynchronizer = null;
var loggedNotes = 0;
var noteMax = 30;
var noteIndex = 0;
var noteType = noteTypePiano;
var noteNameSelected = unselectedNote;
var alreadyLoaded = false;
var expectedPianoNotes = [];
var expectedSinNotes = [];

var selectedPianoNotes = [];
var selectedSinNotes = [];

/* Expected Note definition */
class ExpectedNote {
    constructor(audioElement) {
        var src = audioElement.getAttribute('src');
        var splitted = src.split("/");
        var subdivide = splitted[splitted.length - 1].split("-");
        const noteName = (subdivide[subdivide.length - 1]).replace(".mp3", "").replace("X", "#");
        this._noteName = noteName;
        this._audioElement = audioElement;
    }
    get noteName() {
        return this._noteName;
    }
    get audioElement() {
        return this._audioElement;
    }
}

/* Input Selected Note definition */
class SelectedNote {
    constructor(noteName, reactionTime) {
        this._noteName = noteName;
        this._reactionTime = reactionTime;
    }
    get noteName() {
        return this._noteName;
    }
    get reactionTime() {
        return this.reactionTime;
    }
}

/* View template synchronizer */
function createViewSyncronizer() {
    viewSynchronizer = createTemplateSynchronizer(function (onEveryFrame) {
        
        if (audiosFetched == audiosSources.length && !alreadyLoaded){
            alreadyLoaded = true;
            enableTestButton("Click para comenzar la prueba");
        }

        if (isRunning && !isDone) {
            var timeDelta = Date.now() - timeStart;
            timeCurrent = timeDelta / 1000;
            onEveryFrame.time_current_string = toHHMMSS(Math.floor(timeCurrent));
            onEveryFrame.note_current = noteIndex + 1;
        }
        onEveryFrame.note_type = noteType;
    });

    disableTestButton("Cargando notas...");
    viewSynchronizer.note_current = 1;
    viewSynchronizer.note_max = noteMax;
    viewSynchronizer.time_current_string = toHHMMSS(Math.floor(0));
}

/* Load all audios from remote */
function loadNotesFromRemote() {
    for (const source of audiosSources) {
        var audioElement = document.createElement('audio');
        audioElement.setAttribute('src', source);
        audioElement.addEventListener('ended', nextNote, false);
        audioElement.addEventListener('canplay', function () {
            audiosFetched++;
        }, false);
        audiosInMemory.push(audioElement);
    }
}

/* Only if in simple mode */
function customCSSForSimpleMode() {
    if ("%mode%" == "simple") {
        $('div[class^="v-piano-key"]').each(function () {
            $(this).html($(this).attr("id"));
        });
        $("<br>").insertAfter("#D\\#, #G, #B");
    }
}

/* When a phase is completed */
function phaseCompleted() {
    if (noteType == noteTypePiano) {
        noteIndex = 0;
        noteType = noteTypeSin;
        playNote(noteIndex);
    } else if (noteType == noteTypeSin) {
        viewSynchronizer.note_current = noteMax;
        uploadTest();
    }
}

/* Build expected notes */
function buildExpectedNotes() {
    var temporal = [];

    for (const audioElement of audiosInMemory) {
        temporal.push(new ExpectedNote(audioElement));
    }

    var slicePoint = 69;
    expectedPianoNotes = temporal.slice(0, slicePoint);
    expectedSinNotes = temporal.slice(slicePoint);

    expectedPianoNotes = shuffleArray(expectedPianoNotes).slice(0, noteMax);
    expectedSinNotes = shuffleArray(expectedSinNotes).slice(0, noteMax);
}

/* User just pressed a note */
function selectNote(noteName) {
    noteNameSelected = noteName;

    reactionTime = (Date.now() - timeReference) * 0.96;
    reactionTime = parseFloat(reactionTime).toFixed(2);

    timeReference = Date.now();
}

/* When a next Note is triggered */
function nextNote() {
    noteIndex++;

    logNote(noteNameSelected, reactionTime);

    noteNameSelected = unselectedNote;
    reactionTime = 0;
    timeReference = Date.now();

    if (noteIndex == noteMax) {
        phaseCompleted();
    } else {
        playNote(noteIndex);
    }
}

/* Play current note */
function playNote(index) {
    if (noteType == noteTypePiano) {
        expectedPianoNotes[index].audioElement.play();
    } else if (noteType == noteTypeSin) {
        expectedSinNotes[index].audioElement.play();
    }
}

/* Stop current note */
function stopNote(index) {
    if (noteType == noteTypePiano) {
        expectedPianoNotes[index].pause();
        expectedPianoNotes[index].currentTime = 0;
    } else if (noteType == noteTypeSin) {
        expectedSinNotes[index].pause();
        expectedSinNotes[index].currentTime = 0;
    }
}

/* Log one note to the Note Logger*/
function logNote(noteName, reactionTime = 0) {

    if (noteType == noteTypePiano) {
        selectedPianoNotes.push(new SelectedNote(noteName, reactionTime));
    } else if (noteType == noteTypeSin) {
        selectedSinNotes.push(new SelectedNote(noteName, reactionTime));
    }

    $("#piano_log").css("opacity", "100%");
    $("#piano_log_title").css("opacity", "100%");

    template = $("#note_log_template").clone();
    template.attr("id", "note_log_" + loggedNotes++);
    templateContents = $("#note_log_template > div").clone();
    templateContents.html(noteName);
    if (reactionTime > 0) {
        templateContents.html(noteName + " con duración de " + reactionTime + " ms");
    }

    template.html(templateContents);

    template.show();
    template.appendTo("#piano_log > div");

    var out = $("#piano_log")[0];
    out.scrollTop = out.scrollHeight - out.clientHeight;
}

/* How the engine should prepare the piano */
function loadPiano() {
    customCSSForSimpleMode();
    loadNotesFromRemote();
    buildExpectedNotes();
    createViewSyncronizer();
    $("#note_log_template").hide();
    $("#button_pause").hide();
}


/* Start or stop the test */
function startOrStop() {
    if (!isDone) {
        isRunning = !isRunning;
        if (isRunning) {
            startTest();
        } else {
            stopTest();
        }
    }
}

/* Starts the test */
function startTest() {

    if (shouldRestart) {
        disableTestButton("Reiniciando prueba...");
        window.location.reload();
    } else {

        timeStart = Date.now();

        noteIndex = 0;
        notesType = noteTypePiano;
        viewSynchronizer.button_running_string = "Detener la prueba";

        noteNameSelected = unselectedNote;
        reactionTime = 0;
        timeReference = Date.now();

        playNote(noteIndex);
    }
}

/* Stops the test */
function stopTest() {
    for (i = 0; i < audiosInMemory.length; i++) {
        audiosInMemory[i].currentTime = 0;
        audiosInMemory[i].pause();
    }
    shouldRestart = true;
    viewSynchronizer.button_running_string = "Reintentar la prueba";
}

/* Upload the test */
function uploadTest() {
    isDone = true;
    disableTestButton("Subiendo resultados...");

    expectedNotes = JSON.stringify(expectedPianoNotes.concat(expectedSinNotes));
    selectedNotes = JSON.stringify(selectedPianoNotes.concat(selectedSinNotes));

    $.ajax({
        type: "post",
        url: __URL__ + "piano_submit",
        contentType: 'application/x-www-form-urlencoded',
        data: {mode: "%mode%", expected_notes: expectedNotes, selected_notes: selectedNotes},
        success: function(response) {
            /* he expects a JSON with the response of the token id to redirect to result */
            console.log(response);
        },
    });
}

/* Disable button with text */
function disableTestButton(text) {
    $("#startOrStop").addClass("bg-dark");
    $("#startOrStop").attr("disabled", true);
    viewSynchronizer.button_running_string = text;
}

/* Enable button with text */
function enableTestButton(text) {
    $("#startOrStop").removeClass("bg-dark");
    $("#startOrStop").attr("disabled", false);
    viewSynchronizer.button_running_string = text;
}

loadPiano();