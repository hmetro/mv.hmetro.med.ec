(function($) {
    'use strict';
    $(function() {
        setDateTime();
        setInterval(setDateTime, 3000);

        function setDateTime() {
            var mom = moment();
            mom.locale('es');
            $('.watch').text(mom.format('LT'));
            $('.date').html(mom.format('dddd, DD') + ' <t class="text-lowercase">de</t> ' + mom.format('MMMM') + ' <t class="text-lowercase">de</t> ' + mom.format('YYYY'));
        }
        String.prototype.capitalize = function() {
            return this.replace(/(?:^|\s)\S/g, function(a) {
                return a.toUpperCase();
            });
        };
    });
}(jQuery))