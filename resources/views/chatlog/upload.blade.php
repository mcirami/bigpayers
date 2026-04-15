@extends('layouts.master')
@section('content')
    <!--right_panel-->
    <div class="right_panel">
        <div class="white_box_outer">
            <div class="heading_holder value_span9"><span class="lft">Log Sale for '<?= $offer->offer_name ?>'</span>
            </div>
            <div class="white_box value_span8">

                <form action="/chat-log/upload" method="post" id="form" enctype="multipart/form-data">
                    {{csrf_field()}}

                    <input type="hidden" name="pendingConversionId" value="{{$pendingConversion->id}}">

                    <div class="left_con01">

                        <p>
                            <label class="value_span9">Sale Timestamp</label>
                            <input type="text" value="{{$pendingConversion->timestamp}} " disabled>
                        </p>


                        <p id="imageContainer">

                            <label class="value_span9">Add Images</label>
                            <button class="btn btn-default btn-sm" style="margin-bottom: 5px;"
                                    onclick="addImageInput(); return false;">Add Image
                            </button>
                        <div class="input-group ">
                            <input class="form-control " type="file" name="images[]" accept="image/*"><br>
                        </div>

                        </p>

                    </div>


                    <div class="right_con01">
                    <span class="btn_yellow"> <input type="submit" name="button"
                                                     class="value_span6-2 value_span2 value_span1-2" value="Log Sale"
                                                     onclick=""/></span>
                    </div>
            </div>

        </div>
    </div>


@endsection
@section('footer')
    <script type="text/javascript">
      var counter = 1;

      function addImageInput() {
        counter++;
        if (counter >= 15) {
          alert('yarly');
        }
        $('#imageContainer').append('\t<div class = "input-group " id="img_' + counter + '">\n' +
            '\t\t\t\t\t\t\t<input class = "form-control " type = "file" name = "images[]" accept = "image/*"><br>\n' +
            '\t\t\t\t\t\t\t<span class = "input-group-btn">\n' +
            '\t\t\t\t\t\t\t\t<a href = "#" class = "btn btn-sm btn-danger" onclick=\'removeImageInput(' + counter +
            ');\'>X</a>\n' +
            '\t\t\t\t\t\t</span>\n' +
            '\t\t\t\t\t\t</div>');
      }

      function removeImageInput(num) {
        $('#img_' + num).remove();
      }

    </script>

@endsection
