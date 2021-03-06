@extends('testLayout')

@section('content')


<div class="page-content">
    <div class="container">

					<ul class="page-breadcrumb breadcrumb">
					    <li>
					        <a href="{{ url('test-home-page') }}">Home</a>
					        <i class="fa fa-circle"></i>
					    </li>
					    @if(count($breadcrums) > 0)
					    	@php
					    		$i = 1;
					    	@endphp					    	
						    @foreach($breadcrums as $breadcrum)						    
						    <li>
						    	@if($i == count($breadcrums))
							        <span>{{ $breadcrum['title'] }}</span>
						        @else
							        <a href="{{ $breadcrum['link'] }}">{{ $breadcrum['title'] }}</a>
							        <i class="fa fa-circle"></i>						        
						        @endif
						    </li>
					    	@php
					    		$i++;
					    	@endphp					    							    
						    @endforeach
				    	@endif
					</ul>

					<div class="row">
			        	@if(isset($featured_deals) && count($featured_deals) > 0)
			        	<div class="col-md-12">
				            <div class="clearfix"></div>    
				            <div class="portlet box green">
				                <div class="portlet-title">
				                    <div class="caption">
				                        <i class="fa fa-list"></i> Featured Deals
				                    </div>
				                </div>
				                <div class="portlet-body">                    
						        		<table class="table table-bordered">
						        			<tbody>
						        				<tr>
						        				@foreach($featured_deals as $featured_deal)			        					
						        						<td class="text-center">
						        							@php
					        									$photo = \App\Models\DealPhotos::where("deal_id",$featured_deal->id)->first();
						        							@endphp

						        							@if($photo)
						        							<a href="{{ $featured_deal->link }}" target="_blank">
						        								<img src="{{ $photo->image_url }}" style="max-width: 100px;" />
						        								<br />
						        								{{ $featured_deal->title }}
						        							</a>
						        							@endif
						        						</td>
						        				@endforeach
						        				</tr>
						        			</tbody>
						        		</table>	                	
								</div>					
							</div>	

			        	</div>
			        	@endif						
					</div>

                    <div class="row">
                        <div class='col-md-3'>
                            <h4>
                                @if($rows->total() > 0)
                                <?php 
                                $total = $rows->total();
                                if($rows->currentPage() > 1)
                                {
                                    $startRow = ($rows->currentPage() * $rows->perPage()) - $rows->perPage() + 1;                            
                                    $endRow = $rows->currentPage() * $rows->perPage();                                                                

                                    if($endRow > $total)
                                    {
                                        $endRow = $total;
                                    }    
                                }   
                                else
                                {
                                    $startRow = 1;                            
                                    $endRow = $rows->perPage();     
                                    if ($endRow > $total) {
                                        $endRow = $total;
                                    }

                                }
                                ?>
                                Showing {{ $startRow }} to {{ $endRow }} of {{ number_format($total) }}
                                @endif
                            </h4>
                        </div>
                        <div class='col-md-9 pagination-area text-right'>                    
                            {!! $rows->appends([])->render() !!}
                        </div>                
                    </div>                            

        <div class="row">
        	<div class="col-md-12">
	            <div class="clearfix"></div>    
	            <div class="portlet box green">
	                <div class="portlet-title">
	                    <div class="caption">
	                        <i class="fa fa-list"></i> Products - {{ $page_title or ''}}    
	                    </div>
	                </div>
	                <div class="portlet-body">                    
	                    <table class="table table-bordered table-striped table-condensed flip-content" id="server-side-datatables">
	                        <thead>
	                            <tr>
	                               <th width="20%">Image</th>                           
	                               <th width="30%">Title</th>                                   	                               
	                               <th width="20%">Price</th>
	                               <th width="30%">Attributes</th>
	                            </tr>
	                        </thead>                                         
	                        <tbody>
	                        	@if($rows->total() > 0)
	                        		@foreach($rows as $row)
	                        			<tr>
	                        				<td>
	                        					@php
	                        						$photo = $row->image;
	                        						$link = "#";
	                        						if($row->map_id > 0)
	                        						{
	                        							$obj = \App\Models\Product::find($row->map_id);
	                        							if($obj)
	                        							{
	                        								$link = $obj->link;
	                        						    }
	                        						}
	                        					@endphp

	                        					@if(!empty($photo))
		                        					<a href="{{ $link }}" target="_blank">
		                        						<img src="{{ $photo }}" class="img-responsive" alt="image"/>
		                        					</a>
	                        					@endif
	                        				</td>
	                        				<td>
	                        					<a href="{{ $link }}" target="_blank">
	                        						{{ $row->title }}
	                        					</a>
	                        				</td>
	                        				<td>	                        					
	                        					{{ $row->msrp }}	                        					
	                        				</td>
	                        				<td>
	                        					@php
	                        						$options = \App\Models\FinalProductAttribute::whereIn("keyname",["type","action","barrel","rate-of-twist","stock","# of mags","finish"])
	                        								->where("product_id",$row->id)
	                        								->get();				
	                        					@endphp
	                        					@foreach($options as $option)
	                        						<b>{{ $option->keyname }}: </b>{{ $option->keyvalue }}<br />
	                        					@endforeach
	                        				</td>
	                        			</tr>
	                        		@endforeach
	                        	@else
	                        	<tr>
	                        		<td colspan="10">
	                        			No Products Found !
	                        		</td>
	                        	</tr>
	                        	@endif
	                        </tbody>
	                    </table>                                              
	                </div>
	            </div>                      		
        	</div>
        </div>	

        <div class="row">
            <div class="col-md-12">
                <div class="row">            
                    <div class='col-md-12 pagination-area text-right'>                    
                        {!! $rows->appends([])->render() !!}
                    </div>            
                </div>
            </div>  
        </div>                    

    </div>
</div>    
@endsection