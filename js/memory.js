(function ($) {
    function shuffle(array) {
        for (var i = array.length - 1; i > 0; i--) {
            var j = Math.floor(Math.random() * (i + 1));
            var temp = array[i];
            array[i] = array[j];
            array[j] = temp;
        }
        return array;
    }

    function getImageUrl(word) {
        if (word.image_url) {
            return word.image_url;
        }
        if (word.image) {
            return 'images/' + word.image;
        }
        return '';
    }

    function buildCards(words, mode, pairs) {
        var list = words.slice(0);
        shuffle(list);
        if (pairs > 0 && pairs < list.length) {
            list = list.slice(0, pairs);
        }

        var cards = [];
        list.forEach(function (word) {
            var imageUrl = getImageUrl(word);
            if (mode === 'mixed') {
                if (imageUrl) {
                    cards.push({ matchId: word.id, type: 'image', name: word.name, imageUrl: imageUrl });
                    cards.push({ matchId: word.id, type: 'text', name: word.name, imageUrl: '' });
                } else {
                    cards.push({ matchId: word.id, type: 'text', name: word.name, imageUrl: '' });
                    cards.push({ matchId: word.id, type: 'text', name: word.name, imageUrl: '' });
                }
            } else if (mode === 'text') {
                cards.push({ matchId: word.id, type: 'text', name: word.name, imageUrl: '' });
                cards.push({ matchId: word.id, type: 'text', name: word.name, imageUrl: '' });
            } else {
                if (imageUrl) {
                    cards.push({ matchId: word.id, type: 'image', name: word.name, imageUrl: imageUrl });
                    cards.push({ matchId: word.id, type: 'image', name: word.name, imageUrl: imageUrl });
                } else {
                    cards.push({ matchId: word.id, type: 'text', name: word.name, imageUrl: '' });
                    cards.push({ matchId: word.id, type: 'text', name: word.name, imageUrl: '' });
                }
            }
        });

        return shuffle(cards);
    }

    function renderCards($board, cards) {
        $board.empty();
        
        var columns = calculateOptimalColumns(cards.length);
        $board.css('grid-template-columns', 'repeat(' + columns + ', 120px)');
        
        cards.forEach(function (card, index) {
            var backContent = card.type === 'image' && card.imageUrl
                ? '<img src="' + card.imageUrl + '" alt="' + card.name + '">'
                : '<span class="memory-text">' + card.name + '</span>';

            var cardHtml = '' +
                '<div class="memory-card" data-index="' + index + '" data-match="' + card.matchId + '">' +
                    '<div class="memory-card-inner">' +
                        '<div class="memory-card-front">?</div>' +
                        '<div class="memory-card-back">' + backContent + '</div>' +
                    '</div>' +
                '</div>';
            $board.append(cardHtml);
        });
    }

    function calculateOptimalColumns(cardCount) {
        if (cardCount <= 4) return 2;
        if (cardCount <= 6) return 3;
        if (cardCount <= 12) return 4;
        if (cardCount <= 20) return 5;
        if (cardCount <= 30) return 6;
        return 8;
    }

    $(document).ready(function () {
        var $board = $('#memory-board');
        if ($board.length === 0) {
            return;
        }

        var idsRaw = ($board.data('ids') || '').toString();
        var mode = ($board.data('mode') || 'image').toString();
        var pairs = parseInt($board.data('pairs'), 10) || 0;

        if (!idsRaw) {
            $('#memory-info').removeClass('alert-info').addClass('alert-warning').text('Keine Wörter ausgewählt.');
            return;
        }

        var timerId = null;
        var timeSeconds = 0;
        var moves = 0;
        var matched = 0;
        var lock = false;
        var firstCard = null;
        var secondCard = null;
        var cardsData = [];

        function updateStats() {
            $('#memory-moves').text(moves);
            $('#memory-time').text(timeSeconds);
        }

        function startTimer() {
            if (timerId) {
                return;
            }
            timerId = setInterval(function () {
                timeSeconds++;
                updateStats();
            }, 1000);
        }

        function resetGame() {
            if (timerId) {
                clearInterval(timerId);
            }
            timerId = null;
            timeSeconds = 0;
            moves = 0;
            matched = 0;
            lock = false;
            firstCard = null;
            secondCard = null;
            $('#memory-result').hide().text('');
            updateStats();
            renderCards($board, cardsData);
        }

        function finishGame() {
            if (timerId) {
                clearInterval(timerId);
            }
            timerId = null;
            $('#memory-result').show().text('Geschafft in ' + moves + ' Zügen und ' + timeSeconds + ' Sekunden.');
        }

        function handleCardClick($card) {
            if (lock || $card.hasClass('matched') || $card.hasClass('is-flipped')) {
                return;
            }

            startTimer();
            $card.addClass('is-flipped');

            if (!firstCard) {
                firstCard = $card;
                return;
            }

            secondCard = $card;
            lock = true;
            moves++;
            updateStats();

            var isMatch = firstCard.data('match') === secondCard.data('match');

            setTimeout(function () {
                if (isMatch) {
                    firstCard.addClass('matched');
                    secondCard.addClass('matched');
                    matched += 2;
                    if (matched === cardsData.length) {
                        finishGame();
                    }
                } else {
                    firstCard.removeClass('is-flipped');
                    secondCard.removeClass('is-flipped');
                }

                firstCard = null;
                secondCard = null;
                lock = false;
            }, 600);
        }

        $('#memory-restart').on('click', function () {
            resetGame();
        });

        $board.on('click', '.memory-card', function () {
            handleCardClick($(this));
        });

        $.ajax({
            url: 'memory_data.php',
            method: 'POST',
            dataType: 'json',
            data: {
                ids: idsRaw.split(','),
                mode: mode,
                pairs: pairs
            }
        }).done(function (response) {
            if (!response || !response.ok) {
                $('#memory-info').removeClass('alert-info').addClass('alert-warning').text('Konnte Memory-Daten nicht laden.');
                return;
            }
            cardsData = buildCards(response.words || [], response.mode || mode, response.pairs || pairs);
            if (cardsData.length < 4) {
                $('#memory-info').removeClass('alert-info').addClass('alert-warning').text('Bitte mindestens 2 Wörter auswählen.');
                return;
            }
            renderCards($board, cardsData);
            updateStats();
        }).fail(function () {
            $('#memory-info').removeClass('alert-info').addClass('alert-warning').text('Konnte Memory-Daten nicht laden.');
        });
    });
})(jQuery);
