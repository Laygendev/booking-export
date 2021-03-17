
class PostTypeResource {
	constructor() {
	 	jQuery(document).on('click', '.post-type-owner .add-resource', this.addResource);
	 	jQuery(document).on('click', '.post-type-owner .delete-resource', this.deleteResource);
	}

	addResource(evt) {
		evt.preventDefault();

		var clone = jQuery('.post-type-owner .bloc-form-to-duplicate:last').clone();

		var n = parseInt(clone.data('number'));
		n++;

		clone.find('.key').html(n + 1);

		clone.find('input, select').each(function() {
			var name = jQuery(this).attr('name');
			name = name.replace('[' + (n - 1) + ']', '[' + n + ']');
			
			jQuery(this).attr('name', name);

			var id = jQuery(this).attr('id');
			id = id.replace('_' + id);

			jQuery(this).attr('id', id);
		});

		clone.find('label').each(function() {
			var newFor = jQuery(this).attr('for');
			newFor = newFor.replace('_' + newFor);

			jQuery(this).attr('for', newFor);
		});

		clone.attr('data-number', n);

		jQuery('.post-type-owner .sn-form').append(clone);
	}

	deleteResource(evt) {
		evt.preventDefault()

		if(window.confirm("Confirmer la suppression")) {
			jQuery(this).closest('.bloc-form').fadeOut(400, function() {
				jQuery(this).remove();
			})
		}
	}
}

class ExportTable {
	baseUrl

	constructor() {
		this.baseUrl = jQuery('.wpbc_dashboard .content .export-selected').attr('href');

		jQuery(document).on('click', '.wpbc_dashboard .content table th input[type="checkbox"]', this.selectAll);
		jQuery(document).on('click', '.wpbc_dashboard .content table td input[type="checkbox"]', this.selectEntry);
	}

	selectAll = (evt) => {
		jQuery('.wpbc_dashboard .content table td input[type="checkbox"]').prop("checked", jQuery(evt.target).is(':checked'));
		jQuery('.wpbc_dashboard .content table th input[type="checkbox"]').prop("checked", jQuery(evt.target).is(':checked'));

		this.updateButtonExport();
	}

	selectEntry = () => {
		this.updateButtonExport();
	}

	updateButtonExport() {
		if(jQuery('.wpbc_dashboard .content table td input[type="checkbox"]:checked').length > 0) {
			jQuery('.wpbc_dashboard .content .export-selected').removeClass('disabled');
			var ids = [];
			jQuery('.wpbc_dashboard .content table td input[type="checkbox"]:checked').each((index, item) => {
				ids.push(jQuery(item).val());
			});

			var url = this.baseUrl + "&ids=" + ids.join('-');
			jQuery('.wpbc_dashboard .content .export-selected').attr('href', url);
		} else {
			jQuery('.wpbc_dashboard .content .export-selected').addClass('disabled');

			
		}
	}

	goExport() {

	}
}

(function( $ ) {
	'use strict';

	new PostTypeResource();
	new ExportTable();

	if ($.fn.DataTable) {
		$('.table-datatable').DataTable({
			"language": {
				"lengthMenu": "Afficher _MENU_ éléments par page",
				"zeroRecords": "Aucun élement",
				"info": "Page _PAGE_/_PAGES_",
				"infoEmpty": "Aucun élement disponible",
				"infoFiltered": "(Filtrer sur _MAX_ élements au total)",
				"search": "Rechercher : ",
				"paginate": {
					"previous": "Précédent",
					"next": "Suivant"
				}
			}
		});
	}

	$(".datepicker").datepicker({
		dateFormat: "dd/mm/yy"
	});


	$('[data-toggle="tooltip"]').tooltip()
})( jQuery );