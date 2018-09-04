<div class="portlet box blue">
    <div class="portlet-title">
        <div class="caption">
            <i class="fa fa-search"></i>Advance Search 
        </div>
        <div class="tools">
            <a href="javascript:;" class="expand"> </a>
        </div>                    
    </div>
    <div class="portlet-body" style="display: none">  
        <form id="search-frm">
            <div class="row">
                <div class="col-md-3">
                    <label class="control-label">GR ID</label>
                    <input type="text" value="{{ \Request::get("search_gr_id") }}" class="form-control" name="search_gr_id" />                     
                </div>
                <div class="col-md-3">
                    <label class="control-label">Title</label>
                    <input type="text" value="{{ \Request::get("search_title") }}" class="form-control" name="search_title" />                     
                </div>                
                <div class="col-md-3">
                    <label class="control-label">Category</label>
                    {!! Form::select('search_category', [''=>'Select Category'] + $sources, null, ['class' => 'form-control']) !!}
                </div>
                <div class="col-md-3">
                    <input type="hidden" name="record_per_page" id="record_per_page" />
                    <input type="submit" class="btn blue mTop25" value="Search" />
                    &nbsp;
                    <a href="{{ $list_url }}" class="btn red mTop25">Reset</a>                           
                </div>                
            </div>                                                   
        </form>
    </div>    
</div>    