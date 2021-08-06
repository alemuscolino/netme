# It defines the annotation structure:
#   spot: real term into the text
#   start_pos, and_pos: real position of the term into the text
#   Word: real mean into tagme
def create_annotation_structure(record, old_annotation, text):
    return {
        "wid"        : record["wid"],
        "spot"       : record["spot"],
        "start_pos"  : record["start_pos"],
        "end_pos"    : record["end_pos"],
        "categories" : set(record["categories"]),
        "Word"       : {record["Word"]} if record["Word"] is not None else set(),
        "rho"        : record["rho"]
    }


def create_annotation_struct_1(text, position):
    return {
        "wid"        : "-1",
        "spot"       : text,
        "start_pos"  : position,
        "end_pos"    : position + len(text),
        "categories" : {"entity"},
        "Word"       : text,
        "rho"        : 0
    }


def create_annotation_struct_2(text, position, annotation):
    return {
        "wid"        : "-1",
        "spot"       : text,
        "start_pos"  : position,
        "end_pos"    : position + len(text),
        "categories" : annotation["categories"],
        "Word"       : annotation["Word"],
        "rho"        : 0
    }


# It update an annotation on the basis of the new data (used during annotation joining)
def update_annotation_structure(record, old_annotation, text):
    condition_1 = old_annotation["start_pos"] < record["start_pos"]
    condition_2 = old_annotation["end_pos"] < record["end_pos"]
    start_pos = old_annotation["start_pos"] if condition_1 else record["start_pos"]
    end_pos = record["end_pos"] if condition_2 else old_annotation["end_pos"]

    old_annotation["spot"]       = text[old_annotation["start_pos"]:record["end_pos"]]
    old_annotation["end_pos"]    = record["end_pos"]
    old_annotation["categories"] = old_annotation["categories"].union(set(record["categories"]))
    old_annotation["Word"]       = old_annotation["Word"].union([record["Word"]])
    return old_annotation


def update_annotation_structure_1(text, min_pos, max_pos, annotation):
    annotation["spot"]       = text[min_pos:max_pos]
    annotation["start_pos"]  = min_pos
    annotation["end_pos"]    = max_pos
    annotation["categories"] = {"entity"}
    annotation["Word"]       = annotation["spot"]


# It create or update an annotation structure
def annotation_structure_handler(record, custom_function, old_annotation=None, text=None):
    return custom_function(record, old_annotation, text)


def word_elaboration(annotation):
    if len(annotation["Word"]) == 1:
        annotation["Word"] = list(annotation["Word"])[0]
    else:
        annotation["Word"] = annotation["spot"]
        annotation["categories"] = {"entity"}


# It merge adjacent terms
def joining_annotation(annotations_dict, text, verbs_idx):
    joined_annotations = []
    last_annotation    = None
    last_position      = -10
    for annotation_data in annotations_dict:
        # we remove all the verbs returned by Onotagme
        if annotation_data['start_pos'] in verbs_idx and "other" in annotation_data['categories']: continue
        # IF the difference is less than 2, then the two words are adjacent
        if annotation_data['start_pos'] - last_position < 2:
            # IF the new annotation is into the last one, we skip the new.
            if last_annotation['start_pos'] <= annotation_data['start_pos'] and \
                    annotation_data['end_pos'] <= last_position: continue
            last_annotation = annotation_structure_handler(annotation_data, update_annotation_structure,last_annotation, text)
        # ELSE we stored the previous combination, and then we create the new one
        else:
            if last_annotation is not None:
                # IF the Word set is composed of a single word, then it is the candidate word.
                # ELSE Word will be set to spot (many Words mean no term into TagME)
                word_elaboration(last_annotation)
                joined_annotations.append(last_annotation)
            last_annotation = annotation_structure_handler(annotation_data, create_annotation_structure)
        last_position = annotation_data['end_pos']
    return joined_annotations


# It creates an index over the annotations words
def create_annotation_index(elaborated_annotations):
    # annotation word x position: annotation start pos
    annotations_index  = dict()
    # annotation start pos: {spot:value, vector_pos: elaborated annotations idx}
    reverse_annotation = dict()
    for pos, annotation in enumerate(elaborated_annotations):
        s_pos = annotation["start_pos"]
        annotations_index[s_pos]  = s_pos
        reverse_annotation[s_pos] = {"spot": annotation["spot"], "vector_pos": pos}
        for idx in [i for i, ch in enumerate(annotation["spot"]) if ch == " "]:
            annotations_index[s_pos + idx + 1] = s_pos
    return annotations_index, reverse_annotation
