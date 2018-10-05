(function (exports) {

  function HotList($nav_button, $panel) {

    this.$nav_button = $nav_button;
    this.$panel = $panel;
    this.fixable = $nav_button.data('fixable');
    this.latched = false;

    this.autoHideWidthPixels = 1800;

    // The date to restrict he closed list to. Default to today
    this.selected_date = new Date();

    this.create();
  }

  HotList.prototype.create = function () {
    var hotlist = this;

    if ($('#js-nav-hotlist-btn').length === 0) {
      return;
    }

    $(window).resize(function () {
      hotlist.onBrowserSizeChange();
    });
    hotlist.onBrowserSizeChange();

    this.$nav_button.on('click', function () {
      if (hotlist.isFixable()) {
        return;
      }
      hotlist.latched = !hotlist.latched;
      hotlist.$panel.toggle(hotlist.latched);
    });

    this.$nav_button.on('mouseover', function () {
      hotlist.$panel.show();
    });

    this.$nav_button.on('mouseout', function () {
      if (!hotlist.isFixable() && !hotlist.latched) {
        hotlist.$panel.hide();
      }
    });

    $('.activity-list').find('textarea').autosize();

    // activity datepicker using pickmeup.
    // CSS controls it's positioning
    var $pmuWrap = $('#js-pickmeup-datepicker').hide();
    var pmu = pickmeup('#js-pickmeup-datepicker', {
      format: 'a d b Y',
      flat: true,
      position: 'left'
    });

    // vanilla:
    var activityDatePicker = document.getElementById("js-pickmeup-datepicker");

    // When the pickmeup date picker is changed
    activityDatePicker.addEventListener('pickmeup-change', function (e) {
      $pmuWrap.hide();
      hotlist.setSelectedDate(e.detail.date, e.detail.formatted_date);
      hotlist.updateClosedList();
    });


    // When the date picker is clicked
    $('#js-hotlist-closed-select').click(function () {
      $pmuWrap.toggle();
      return false;
    });

    // Hide the date picker if anywhere else on the screen is clicked
    $('body').on('click', function (e) {
      if (!$(e.target).closest('#js-pickmeup-datepicker').length) {
        $('#js-pickmeup-datepicker').hide();
      }
    });

    // When the "Today" date link is clicked
    $('#js-hotlist-closed-today').click(function () {
      pmu.set_date(new Date);
      hotlist.setSelectedDate(new Date, 'Today');
      hotlist.updateClosedList();
    });

    // When a patient record is clicked
    $('.activity-list').delegate('.js-hotlist-closed-patient a', 'click', function () {
      var closedPatient = $(this).closest('.js-hotlist-closed-patient');
      $.ajax({
        type: 'GET',
        url: '/UserHotlistItem/openHotlistItem',
        data: {hotlist_item_id: closedPatient.data('id')},
        success: function () {
          window.location.href = closedPatient.data('patientHref');
        }
      });

      return false;
    });

    // When the close link in an open item is clicked
    $('.activity-list.closed').delegate('.js-open-hotlist-item', 'click', function () {

      var itemId = $(this).closest('.js-hotlist-closed-patient').data('id');

      $.ajax({
        type: 'GET',
        url: '/UserHotlistItem/openHotlistItem',
        data: {hotlist_item_id: itemId},
        success: function () {
          hotlist.updateOpenList();
        }
      });

      hotlist.removeItem(itemId);
      return false;
    });

    // WHen the open link in a closed item is clicked
    $('.activity-list.open').delegate('.js-close-hotlist-item', 'click', function () {

      var itemId = $(this).closest('.js-hotlist-open-patient').data('id');

      $.ajax({
        type: 'GET',
        url: '/UserHotlistItem/closeHotlistItem',
        data: {hotlist_item_id: itemId},
        success: function () {
          hotlist.updateClosedList();
        }
      });

      hotlist.removeItem(itemId);
      return false;
    });


    var commentUpdateTimeout;
    // When the enter key is pressed when editing a comment
    $('.activity-list').delegate('.js-hotlist-comment textarea', 'keyup', function (e) {
      var comment = $(this).val();
      var itemId = $(this).closest('.js-hotlist-comment').data('id');
      clearTimeout(commentUpdateTimeout);
      commentUpdateTimeout = setTimeout(function () {
        hotlist.updateComment(itemId, comment);
      }, 500);
    });

    // Wjem the comment button in any item is clicked
    $('.activity-list').delegate('.js-add-hotlist-comment', 'click', function () {
      var hotlistItem = $(this).closest('.js-hotlist-open-patient, .js-hotlist-closed-patient');
      var itemId = hotlistItem.data('id');
      var commentRow = hotlistItem.siblings('.js-hotlist-comment[data-id="' + itemId + '"]');
      if (commentRow.css('display') !== 'none') {
        hotlist.updateComment(itemId, commentRow.find('textarea').val());
        commentRow.hide();
      } else {
        commentRow.show();
        commentRow.find('textarea').focus();
      }

      return false;
    });
  };

  HotList.prototype.onBrowserSizeChange = function () {
    if (this.latched) {
      return;
    }

    if ($(window).width() > this.autoHideWidthPixels) { // min width for fixing Activity Panel (allows some resizing)
      this.$panel.toggle(this.isFixable());
    } else {
      this.$panel.hide();
    }
  };

  HotList.prototype.isFixable = function () {
    return this.$nav_button.data('fixable') && $(window).width() > this.autoHideWidthPixels;
  };

  HotList.prototype.setSelectedDate = function (date, display_date) {
    this.selected_date = date;
    if (this.selected_date.toDateString() === (new Date).toDateString()) {
      $('#js-pickmeup-closed-date').text('Today');
    } else {
      $('#js-pickmeup-closed-date').text(display_date);
    }
  };

  HotList.prototype.updateClosedList = function () {
    var hotlist = this;

    $.ajax({
      type: 'GET',
      url: '/UserHotlistItem/renderHotlistItems',
      data: {
        is_open: 0,
        date: this.selected_date.getFullYear() + '-' + (this.selected_date.getMonth() + 1) + '-' + this.selected_date.getDate()
      },
      success: function (response) {
        $('table.activity-list.closed').find('tbody').html(response);
        hotlist.updateListCounters();
        $('.activity-list').find('textarea').autosize();
      }
    });
  };

  HotList.prototype.updateOpenList = function () {
    var hotlist = this;

    $.ajax({
      type: 'GET',
      url: '/UserHotlistItem/renderHotlistItems',
      data: {is_open: 1, date: null},
      success: function (response) {
        $('table.activity-list.open').find('tbody').html(response);
        hotlist.updateListCounters();
        $('.activity-list').find('textarea').autosize();
      }
    });
  };

  HotList.prototype.updateListCounters = function () {
    $('.patients-open .count').text($('.activity-list.open .js-hotlist-open-patient').length);
    $('.patients-closed .count').text($('.activity-list.closed .js-hotlist-closed-patient').length);
  };

  HotList.prototype.removeItem = function (itemId) {
    $('.activity-list tr[data-id="' + itemId + '"]').remove();
    $('body').find(".oe-tooltip").remove();
  };

  HotList.prototype.updateComment = function (itemId, userComment) {
    var hotlistItem = $('.activity-list tr[data-id="' + itemId + '"]');
    var shortComment = userComment.substr(0, 30) + (userComment.length > 30 ? '...' : '');
    var readonlyComment = hotlistItem.find('.js-hotlist-comment-readonly');
    readonlyComment.text(shortComment);
    readonlyComment.data('tooltip-content', userComment);

    var commentIcon = hotlistItem.find('i.js-add-hotlist-comment');
    commentIcon.removeClass('comments comments-added active');
    commentIcon.addClass(userComment.length > 0 ? 'comments-added active' : 'comments');

    $.ajax({
      type: 'GET',
      url: '/UserHotlistItem/updateUserComment',
      data: {hotlist_item_id: itemId, comment: userComment}
    });
  };

  exports.HotList = HotList;

}(OpenEyes.UI));