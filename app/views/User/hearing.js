$(document).ready(function () {

    // Entry Point
    var arguments = $('script[src*=hearing]');
    var url = arguments.attr('data-url');
    var mode = arguments.attr('data-mode');
    var audiosSources = JSON.parse($('#audios_sources').text());

    // Constants
    const noteTypePiano = "1/2 (Piano) Tonos reales ";
    const noteTypeSin = "2/2 (Puros) Tonos artificiales ";
    const unselectedNote = "Sin seleccionar";

    // Variables
    var renderer = new PypeRenderer();

    var timeCurrent = 0;
    var reactionTime = 0;

    var isDone = false;
    var isRunning = false;
    var shouldRestart = false;
    var alreadyLoaded = false;

    var timeStart = Date.now();
    var timeReference = Date.now();

    var audiosFetched = 0;
    var audiosInMemory = [];

    var loggedNotes = 0;
    var noteIndex = 0;
    var noteMax = 30;
    var noteType = noteTypePiano;
    var noteNameSelected = unselectedNote;

    // Form variables
    var expectedPianoNotes = [];
    var expectedSinNotes = [];

    var selectedPianoNotes = [];
    var selectedSinNotes = [];

    // Loads mp3 in memory
    window.loadAudiosFromRemote = function() {
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

    // Build expected notes
    window.buildExpectedNotes = function() {
        var temporal = [];

        for (const audioElement of audiosInMemory) {
            temporal.push(new ExpectedNote(audioElement));
        }

        var slicePoint = 69;
        expectedPianoNotes = temporal.slice(0, slicePoint);
        expectedSinNotes = temporal.slice(slicePoint);

        expectedPianoNotes = JSHelper.shuffleArray(expectedPianoNotes).slice(0, noteMax);
        expectedSinNotes = JSHelper.shuffleArray(expectedSinNotes).slice(0, noteMax);
    }

    // User just pressed a note
    window.selectNote = function(noteName) {
        noteNameSelected = noteName;

        reactionTime = (Date.now() - timeReference) * 0.97;
        reactionTime = parseFloat(reactionTime).toFixed(2);

        timeReference = Date.now();
    }

    // Play current note
    window.playNote = function(index) {
        if (noteType == noteTypePiano) {
            expectedPianoNotes[index].audioElement.play();
        } else if (noteType == noteTypeSin) {
            expectedSinNotes[index].audioElement.play();
        }
    }

    // Stop current note
    window.stopNote = function(index) {
        if (noteType == noteTypePiano) {
            expectedPianoNotes[index].pause();
            expectedPianoNotes[index].currentTime = 0;
        } else if (noteType == noteTypeSin) {
            expectedSinNotes[index].pause();
            expectedSinNotes[index].currentTime = 0;
        }
    }

    // When a next Note is triggered
    window.nextNote = function() {
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

    // Log one note to the Note Logger
    window.logNote = function(noteName, reactionTime = 0) {

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

    // When a phase is completed
    window.phaseCompleted = function() {
        if (noteType == noteTypePiano) {
            noteIndex = 0;
            noteType = noteTypeSin;
            playNote(noteIndex);
        } else if (noteType == noteTypeSin) {

            renderer.render(function(state){
                state.note_current = noteMax;
            });

            uploadTest();
        }
    }

    // Start or stop the test
    window.startOrStop = function() {
        if (!isDone) {
            isRunning = !isRunning;
            if (isRunning) {
                startTest();
            } else {
                stopTest();
            }
        }
    }
    
    // Starts the test
    window.startTest = function() {

        if (shouldRestart) {

            disableTestButton("Reiniciando prueba...");
            window.location.reload();

        } else {

            timeStart = Date.now();

            noteIndex = 0;
            notesType = noteTypePiano;

            renderer.render(function(state){
                state.button_running_string = "Detener la prueba";
            });

            noteNameSelected = unselectedNote;
            reactionTime = 0;
            timeReference = Date.now();

            playNote(noteIndex);
        }
    }

    // Stops the test
    window.stopTest = function() {
        for (i = 0; i < audiosInMemory.length; i++) {
            audiosInMemory[i].currentTime = 0;
            audiosInMemory[i].pause();
        }

        shouldRestart = true;

        renderer.render(function(state){
            state.button_running_string = "Reintentar la prueba";
        });
    }

    // How the engine should prepare the piano
    window.loadPiano = function() {
        loadAudiosFromRemote();
        buildExpectedNotes();
        
        renderer.onStart(function(state){
            state.note_type = 1;
            disableTestButton("Cargando notas...");
            state.note_current = 1;
            state.note_max = noteMax;
            state.time_current_string = JSHelper.numericalToHHMMSS(Math.floor(0));
        });

        renderer.onUpdate(function(state){
            if (audiosFetched == audiosSources.length && !alreadyLoaded){
                alreadyLoaded = true;
                enableTestButton("Click para comenzar la prueba");
            }
    
            if (isRunning && !isDone) {
                var timeDelta = Date.now() - timeStart;
                timeCurrent = timeDelta / 1000;
                state.time_current_string = JSHelper.numericalToHHMMSS(Math.floor(timeCurrent));
                state.note_current = noteIndex + 1;
            }
            state.note_type = noteType;

        });

        $("#note_log_template").hide();
        $("#button_pause").hide();
    }


    // Upload the test
    window.uploadTest = function() {
        isDone = true;
        disableTestButton("Subiendo resultados...");

        expectedNotes = JSON.stringify(expectedPianoNotes.concat(expectedSinNotes));
        selectedNotes = JSON.stringify(selectedPianoNotes.concat(selectedSinNotes));

        $.ajax({
            type: "post",
            url: url + "test/audio/submit",
            contentType: 'application/x-www-form-urlencoded',
            data: {mode: mode, expected_notes: expectedNotes, selected_notes: selectedNotes},
            success: function(response) {
                // he expects a JSON with the response of the token id to redirect to result
                console.log(response);
            },
        });
    }

    // Disable button with text
    window.disableTestButton = function(text) {
        $("#startOrStop").addClass("bg-dark");
        $("#startOrStop").attr("disabled", true);

        renderer.render(function(state){
            state.button_running_string = text;
        });
    }

    // Enable button with text
    window.enableTestButton = function(text) {
        $("#startOrStop").removeClass("bg-dark");
        $("#startOrStop").attr("disabled", false);

        renderer.render(function(state){
            state.button_running_string = text;
        });
    }

    loadPiano();

});

// Expected Note definition
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

// Input Selected Note definition
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